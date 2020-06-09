<?php

namespace App\Http\Controllers\Auth;

use App\Domain\UserType;
use App\Domain\UserTypeFidelity;
use App\Http\Controllers\Controller;
use App\Profile;
use App\User;
use Facebook\Facebook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;


class RegisterController extends Controller
{

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'cpf' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'shopID' => 'required|exists:shop,id'
        ]);
    }

    protected function facebookValidator(array $data)
    {

        return Validator::make($data, [

            'token' => 'required|string',
        ]);
    }

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

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

        $user->profile = $profile;

        $token = JWTAuth::fromUser($user);

        // insert fidelity
        DB::table('user_type_fidelity')->insert(['user_id' => $user->id,
            'user_fidelity' => UserTypeFidelity::CLUBE_ARENA_BABY]);

        DB::table('user_shop')->insert(['user_id' => $user->id,
            'shop_id' => $request['shopID'], 'is_main_link'=>true]);

        return $this->withoutEncryptDataWithToken($user, $token);
    }

    public function getFacebookProfile(Facebook $fb, Request $request)
    {
        $validator = $this->facebookValidator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $response = $fb->get('/me?fields=name,location,email,gender,picture,first_name', $request['token']);

            $location = $response->getGraphUser()->getLocation();

            $user = new User();

            $user->name = $response->getGraphUser()->getFirstName();
            $user->gender = $response->getGraphUser()->getGender();
            $user->photo = $response->getGraphUser()->getPicture()->getUrl();
            $user->email = $response->getGraphUser()->getEmail();
            $user->city = is_null($location) ? $location : $location->getLocation()->getCity();

            return $this->withoutEncryptData($user);

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return $this->jsonError('Authentication error',
                $e->getMessage(), 401);

        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return $this->jsonError('Authentication error',
                $e->getMessage(), 401);
        } catch (Exception $e) {
            return $this->jsonError('Authentication error',
                "Ocorreu um erro no servidor.", 500);
        }
    }

    protected function jsonError($message, $error, $code)
    {
        return response()->json([
            'message' => $message,
            'errors' => $error
        ], $code);
    }

}
