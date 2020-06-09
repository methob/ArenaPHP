<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 16/04/2018
 * Time: 21:51
 */

namespace App\Http\Controllers;

use App\Domain\TransationType;
use App\Domain\UserType;
use App\Profile;
use App\Transaction;
use App\User;
use App\UserShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Integer;

class ClientController extends Controller
{
    protected function byID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $users = DB::table('user_shop')
            ->leftJoin('user_type_fidelity', 'user_shop.user_id', '=', 'user_type_fidelity.user_id')
            ->join('users', 'users.id', '=', 'user_shop.user_id')
            ->select('users.name', 'users.id', 'user_type_fidelity.user_fidelity')
            ->get();

        return $this->withoutEncryptData($users);
    }

    protected function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'profile' => 'required',
            'email' => 'required|unique:users',
            'cpf' => 'required|unique:users',
            'cityID' => 'required',
            'stateID' => 'required',
            'gender' => 'required',
            'neighborhood' => 'required',
            'password' => 'required',
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        // CREATE PROFILE
        $profile = Profile::create([
            'gender' => $request['gender'],
            'city_id' => $request['cityID'],
            'state_id' => $request['stateID'],
            'neighborhood' => $request['neighborhood'],
        ]);

        // CREATE USER
        $user = User::create([
            'name' => $request['name'],
            'cpf' => $request['cpf'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'profile_id' => $profile->id,
            'user_type' => UserType::APP
        ]);

        // insert fidelity
        DB::table('user_type_fidelity')->insert(['user_id' => $user->id,
            'user_fidelity' => $request['profile']]);

        DB::table('user_shop')->insert(['user_id' => $user->id,
            'shop_id' => $request['shop_id'], 'is_main_link' => true]);

        $response = [
            'message' => "Usuário criado com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userTemp = User::find($request['user_id'])->first();
        $profileTemp = Profile::find($userTemp->profile_id)->first();

        $userTemp->profile = $profileTemp;

        $response = new \stdClass();

        $response->user = $userTemp;

        $response->transations = DB::table('transactions')->join('user_shop', function ($join) use ($request) {
            $join->on('transactions.user_shop_id', '=', 'user_shop.id')
                ->where(['user_shop.user_id' => $request['user_id']]);
        })->join('shop', 'shop.id', '=', 'user_shop.shop_id')
            ->select('transactions.created_at', 'transactions.value', 'transactions.id', 'shop.social_name')
            ->where(['transation_type' => TransationType::OUTPUT, 'is_transaction_in_stars' => false])->get();

        $credit = new \stdClass();

        $credit->creditTransations = DB::table('transactions')->join('user_shop', function ($join) use ($request) {
            $join->on('transactions.user_shop_id', '=', 'user_shop.id')
                ->where(['user_shop.user_id' => $request['user_id']]);
        })->select(DB::raw('SUM(transactions.value) as credit'))
            ->where(['transactions.transation_type' => TransationType::INPUT, 'is_transaction_in_stars' => false])
            ->value('credit');

        $credit->stars = DB::table('user_shop')
            ->select(DB::raw('SUM(stars) as stars'))
            ->where(['user_id' => $request['user_id']])
            ->value('stars');

        $credit->stars += $userTemp->credit;

        $credit->transations = DB::table('transactions')->join('user_shop', function ($join) use ($request) {
            $join->on('transactions.user_shop_id', '=', 'user_shop.id')
                ->where(['user_shop.user_id' => $request['user_id']]);
        })->select('value', 'created_at', 'description')
            ->where(['is_transaction_in_stars' => true,
                'transation_type' => TransationType::OUTPUT])
            ->get();

        $response->credit = $credit;

        $response->courses = DB::table('user_courses')
            ->join('courses', 'courses.id', '=', 'user_courses.course_id')
            ->select('courses.title', 'is_finalized')
            ->where(['user_courses.user_id' => $request['user_id']])
            ->get();

        return $this->withoutEncryptData($response);
    }

    protected function getProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpf' => 'required|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = DB::table('users')
            ->join('profiles', 'profiles.id', '=', 'users.profile_id')
            ->join('user_type_fidelity', 'user_type_fidelity.user_id', '=', 'users.id')
            ->select('users.name', 'users.cpf', 'user_type_fidelity.user_fidelity',
                'users.email', 'profiles.neighborhood')
            ->where(['users.cpf' => $request['cpf']])
            ->get();

        return $this->withoutEncryptData($user);
    }

    protected function getTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $transations = DB::table('transactions')->join('user_shop', function ($join) use ($request) {
            $join->on('transactions.user_shop_id', '=', 'user_shop.id')
                ->where(['user_shop.user_id' => $request['user_id']]);
        })->join('shop', 'shop.id', '=', 'user_shop.shop_id')
            ->select('transactions.created_at', 'transactions.value', 'transactions.id', 'transactions.description')
            ->where(['transation_type' => TransationType::OUTPUT,
                'is_transaction_in_stars' => false, 'shop.id' => $request['shop_id']])
            ->get();

        return $this->withoutEncryptData($transations);
    }

    protected function getStars(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $stars = DB::table('user_shop')
            ->select('user_shop.stars')
            ->where(['user_id' => $request['user_id'], 'shop_id' => $request['shop_id']])
            ->get();

        return $this->withoutEncryptData($stars);
    }

    protected function giveStars(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'shop_id' => 'required',
            'quantity' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        DB::table('user_shop')->where(
            ['user_id' => $request['user_id'],
                'shop_id' => $request['shop_id']])
            ->increment('stars', $request['quantity']);

        $response = [
            'message' => "Requisicao realizada com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

//    protected function convertStars(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'id' => 'required',
//            'shop_id' => 'required',
//            'stars' => 'required'
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json([
//                'message' => trans('validation.validate_message'),
//                'errors' => $validator->errors()
//            ], 422);
//        }
//    }

    protected function convertInCredits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required',
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::where(['user_id' => $request['user_id'], 'shop_id' => $request['shop_id']])->first();


        if ($userShop->stars < 15) {

            $response = [
                'message' => "usuário não tem estrelas suficientes!"
            ];

        } else {

            $babycoins = (Integer)$userShop->stars / 15;
            $userShop->current_credit += $babycoins * 60;
            $userShop->stars = $userShop->stars - $babycoins*15;
            $userShop->save();

            $response = [
                'message' => "Crédito adicionado com sucesso!"
            ];
        }

        return $this->withoutEncryptData($response);
    }

    protected function giveCredit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_number' => 'required',
            'value' => 'required',
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::firstOrCreate(['user_id' => $request['user_id'], 'shop_id' => $request['shop_id']]);
        $userShop->current_credit += $request['value'];

        if ($request['value'] % 30 == 0) {
            $stars = 0.5 * $request['value'] / 30;
            $userShop->stars += $stars;

            Transaction::create(['value' => $stars,
                'transation_type' => TransationType::INPUT, 'is_transaction_in_stars' => true,
                'user_shop_id' => $userShop->id, 'description' => 'Ganho de estrelas']);
        }

        $userShop->save();

        Transaction::create(['value' => $request['value'],
            'transation_type' => TransationType::INPUT,
            'is_transaction_in_stars' => false, 'user_shop_id' => $userShop->id, 'description' => 'compra de produto']);

        $response = [
            'message' => "Transação realizada com sucesso!"
        ];

        return $this->withoutEncryptData($response);

    }

    protected function creditOutflow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'shop_id' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::firstOrCreate(['user_id' => $request['user_id'], 'shop_id' => $request['shop_id']]);

        if ($userShop->current_credit < $request['value']) {
            $response = [
                'message' => "Saldo insuficiente."
            ];

        } else {

            $userShop->current_credit -= $request['value'];
            $userShop->save();

            $response = [
                'message' => "Transação realizada com sucesso!."
            ];
        }

        return $this->withoutEncryptData($response);
    }

    protected function giveBabyCoin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'value' => 'required',
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::firstOrCreate(['user_id' => $request['user_id'], 'shop_id' => $request['shop_id']]);
        $userShop->stars += $request['value'] * 15;
        $userShop->save();

        Transaction::create(['value' => ($request['vale'] * 15),
            'transation_type' => TransationType::INPUT, 'is_transaction_in_stars' => true,
            'user_shop_id' => $userShop->id, 'description' => 'Adicao de babycoin']);

        $response = [
            'message' => "Transação realizada com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function removeBabyCoin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'value' => 'required',
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::firstOrCreate(['user_id' => $request['user_id'], 'shop_id' => $request['shop_id']]);

        if ($userShop->stars < $request['value'] * 15) {
            $response = [
                'message' => "Não é possível retirar mais do que o usuário possui!"
            ];

        } else {

            $userShop->stars -= $request['value'] * 15;
            $userShop->save();

            Transaction::create(['value' => ($request['vale'] * 15),
                'transation_type' => TransationType::OUTPUT, 'is_transaction_in_stars' => true,
                'user_shop_id' => $userShop->id, 'description' => 'Retirada']);

            $response = [
                'message' => "Transação realizada com sucesso!"
            ];
        }

        return $this->withoutEncryptData($response);
    }
}