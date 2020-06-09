<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 19/03/2018
 * Time: 15:11
 */

namespace App\Http\Controllers;

use App\Domain\TransationType;
use App\Transaction;
use App\UserCredit;
use App\UserCreditDeclined;
use App\UserShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    private function validator($data)
    {
        return Validator::make($data, [
            'id' => 'required|exists:user_shop,user_id'
        ]);
    }

    public function index(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::where('user_id', $request['id'])->first();

        $transations = Transaction::where('user_shop_id', $userShop->id)
            ->where(['transation_type' => TransationType::OUTPUT, 'is_transaction_in_stars' => false])->get();

        $response = [
            'transations' => $transations,
            'stars' => $userShop->stars,
            'credit' => $userShop->current_creditF
        ];

        return $this->withoutEncryptData($response);
    }

    public function generateCredit(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::where('user_id', $request['id'])->first();

        $credit = UserCredit::where(['user_shop_id' => $userShop->id, 'is_declined' => 0])->sum('value');

        $response = [
            'credit' => $credit,
            'user_shop_id' => $userShop->id
        ];

        return $this->withoutEncryptData($response);
    }

    public function refuseCredit(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::where('user_id', $request['id'])->first();

        $credit = UserCredit::where(['user_shop_id' => $userShop->id, 'is_declined' => false])->sum('value');

        if ($credit == 0) {
            return response()->json([
                'message' => "Usuário não tem crédito a receber.",
            ], 401);
        }

        $userCredits = UserCredit::where(['user_shop_id' => $userShop->id, 'is_declined' => false])->get();

        foreach ($userCredits as $userCredit) {

            UserCreditDeclined::create(['message' => $request['message'],
                'answer_option' => $request['answerOption'],
                'user_credit' => $userCredit->id]);

            $userCredit->update(['is_declined' => true]);
        }

        $response = [
            'message' => 'Crédito recusado com sucesso!'
        ];

        DB::table('user_credit_acceptance')->create(['user_id' => $request['id'], 'is_accepted' => false]);

        return $this->withoutEncryptData($response);
    }

    public function acceptCredit(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::where('user_id', $request['id'])->first();

        $credit = UserCredit::where(['user_shop_id' => $userShop->id, 'is_declined' => false])->sum('value');

        if ($credit == 0) {
            return response()->json([
                'message' => "Usuário não tem crédito a receber.",
            ], 401);
        }

        UserCredit::where(['user_shop_id' => $userShop->id, 'is_declined' => false])->delete();

        Transaction::create(['value' => $credit, 'transation_type' => TransationType::INPUT,
            'user_shop_id' => $userShop->id]);

        $userShop->current_credit += $credit;

        $userShop->save();

        DB::table('user_credit_acceptance')->create(['user_id' => $request['id'], 'is_accepted' => true]);

        $response = [
            'credit' => $userShop->current_credit
        ];

        return $this->withoutEncryptData($response);
    }
}