<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 19/03/2018
 * Time: 16:00
 */

namespace App\Http\Controllers;

use App\City;
use App\Domain\TransationType;
use App\Domain\UserType;
use App\Shop;
use App\State;
use App\User;
use App\UserShop;
use App\UserShopAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    protected function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $shops = Shop::all('nickname', 'number', 'address', 'neighborhood', 'latitude', 'longitude', 'id');

        foreach ($shops as $shop) {

            $shop->distance = $this->distance($request['latitude'], $request['longitude'],
                $shop->latitude, $shop->longitude, 'K');
        }

        return $this->withoutEncryptData($shops);
    }

    protected function all(Request $request) {

        $shop = Shop::all(['id', 'social_name']);
        return $this->withoutEncryptData($shop);
    }

    protected function show(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $shop = Shop::find($request['shop_id'], ['id', 'cep', 'nickname', 'address', 'number', 'complement',
            'neighborhood', 'social_name', 'is_franchise', 'phone_number', 'cellphone', 'facebook',
            'instagram', 'website', 'rating', 'latitude', 'longitude', 'cityID', 'stateID']);

        $state = State::find($shop['stateID']);
        $city = City::find($shop['cityID']);

        $shop->state = $state;
        $shop->city = $city;
        unset($shop['cityID']);
        unset($shop['stateID']);

        $shop->user = DB::table('shop')
            ->join('users', 'users.id', '=', 'shop.user_id')
            ->where(['shop.id'=>$shop['id']])
            ->select(['name', 'email'])
            ->first();

        return $this->withoutEncryptData($shop);
    }

    protected function followShop(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $userShop = UserShop::firstOrCreate(['user_id' => $request['id'], 'shop_id' => $request['shop_id']]);

        $userShop->is_follow = true;

        $userShop->save();

        $response = [
            'message' => "Parabéns, agora você está seguindo as novidades dessa loja!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function incrementAccess(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        UserShopAccess::create(['user_id' => $request['id'], 'shop_id' => $request['shop_id']]);

        $response = [
            'message' => "A Loja foi visualizada."
        ];

        return $this->withoutEncryptData($response);
    }

    protected function billing(Request $request)
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

        $response = new \stdClass();

        $response->clients = DB::table('user_shop')
            ->join('users', 'users.id', '=', 'user_shop.user_id')
            ->join('profiles', 'profiles.id', '=', 'users.profile_id')
            ->select('users.name', 'users.id', 'profiles.gender')
            ->where('user_shop.shop_id', '=', $request['shop_id'])
            ->get();

        $response->stars = DB::table('transactions')
            ->join('user_shop', 'user_shop.id', '=', 'transactions.user_shop_id')
            ->where(['transation_type' => TransationType::OUTPUT,
                'is_transaction_in_stars' => true, 'user_shop.shop_id' => $request['shop_id']])
            ->value(DB::raw('SUM(value) as totalStars'));

        $response->credit = DB::table('transactions')
            ->join('user_shop', 'user_shop.id', '=', 'transactions.user_shop_id')
            ->where(['transation_type' => TransationType::OUTPUT,
                'is_transaction_in_stars' => false, 'user_shop.shop_id' => $request['shop_id']])
            ->value(DB::raw('SUM(value) as value'));


        return $this->withoutEncryptData($response);
    }

    protected function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logradouro' => 'required',
            'neighborhood' => 'required',
            'cityID' => 'required',
            'stateID' => 'required',
            'number' => 'required',
            'cnpj' => 'required|unique:users,cpf',
            'socialName' => 'required|unique:shop,social_name',
            'nickname' => 'required',
            'owner' => 'required',
            'isFranchise' => 'required',
            'email' => 'required|email|unique:users',
            'cellphone' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create(['cpf' => $request['cnpj'],
            'email' => $request['email'],
            'name' => $request['owner'],
            'password' => Hash::make($request['password']),
            'user_type' => UserType::FRANCHISE_ADMIN]);

        $shop = Shop::create(['cep' => $request['cep'],
            'credit' => $request['logradouro'], 'number' => $request['number'],
            'complement' => $request['complement'], 'neighborhood' => $request['neighborhood'],
            'social_name' => $request['cnpj'], 'nickname' => $request['nickname'],
            'is_franchise' => $request['isFranchise'], 'phone_number' => $request['phoneNumber'],
            'cellphone' => $request['cellhone'], 'facebook' => $request['facebook'],
            'instagram' => $request['instagram'], 'website' => $request['website'], 'user_id' => $user->id]);


        // CREATE GALLERY
        if ($request['photos'] != null) {

            foreach ($request['photos'] as $photo) {

                $filename = $this->storeFile($photo);

                DB::table('shop_photos')->insert(['shop_id' => $shop->id, 'filename' => $filename]);
            }
        }

        $response = [
            'message' => "Loja salva com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function link(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required',
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $shop = DB::table('user_shop')
            ->where(['shop_id' => $request['shop_id'], 'user_id' => $request['user_id']])
            ->get();

        if ($shop == null) {

            DB::table('user_shop')
                ->insert(['shop_id' => $request['shop_id'], 'user_id' => $request['user_id'], 'is_main_link' => true]);

            $response = [
                'message' => "Usuário vinculado com sucesso!"
            ];

        } else {

            if ($shop->is_main_link) {

                $response = [
                    'message' => "Você já está vinculado a essa loja."
                ];

            } else {

                $linkShop = DB::shop('user_shop')->
                where(['user_id' => $request['id'], 'is_main_link' => true])
                    ->get();

                if ($linkShop != null) {
                    $response = [
                        'message' => "Usuário já vinculado a outra loja"
                    ];

                } else {
                    $shop->is_main_link = true;
                    $shop->save();
                    $response = [
                        'message' => "Usuário vinculado com sucesso!"
                    ];
                }
            }
        }
        return $this->withoutEncryptData($response);
    }

    private function storeFile($image)
    {
        $filename = $image->store('images', 'public');
        $filename = str_replace('/images', '', $filename);
        return $filename;
    }

    private function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    private function validator($data)
    {
        return Validator::make($data, [
            'id' => 'required|exists:user_shop,user_id',
            'shop_id' => 'required|exists:shop,id'
        ]);
    }
}