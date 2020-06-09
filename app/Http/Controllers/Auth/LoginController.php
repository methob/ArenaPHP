<?php

namespace App\Http\Controllers\Auth;

use App\Domain\UserType;
use App\Http\Controllers\Controller;
use App\Profile;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{

    private function validator($data)
    {
        return Validator::make($data, [
            'cpf' => 'required',
            'password' => 'required'
        ]);
    }

    public function loginGeneralAdmin(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('cpf', 'password');

        $user = User::where('cpf', $credentials['cpf'])->first();

        if (!$user || $user->user_type != UserType::GENERAL_ADMIN) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Validate Password
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Senha incorreta.'
            ], 401);
        }

        // Generate Token
        $token = JWTAuth::fromUser($user);

        return $this->withoutEncryptDataWithToken($user, $token);
    }

    public function loginFranchiseAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cnpj' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('cnpj', 'password');

        $user = User::where('cpf', $credentials['cnpj'])->first();

        if (!$user || $user->user_type != UserType::FRANCHISE_ADMIN) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Validate Password
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Senha incorreta.'
            ], 401);
        }

        $shop = DB::table('shop')->where(['user_id' => $user->id])->first(['id', 'nickname', 'social_name']);

        $user->shop = $shop;

        // Generate Token
        $token = JWTAuth::fromUser($user);

        // save accesses
        DB::table('user_access')->insert(['user_id' => $user->id,
            'user_type' => UserType::FRANCHISE_ADMIN,
            'is_online' => true,
            'token' => $token]);

        return $this->withoutEncryptDataWithToken($user, $token);
    }

    public function loginApp(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('cpf', 'password');

        $user = User::where('cpf', $credentials['cpf'])->first();

        if (!$user || $user->user_type != UserType::APP) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Validate Password
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Senha incorreta.'
            ], 401);
        }

        $user->profile = Profile::where('id', $user->profile_id)->get();

        // Generate Token
        $token = JWTAuth::fromUser($user);

        // save accesses
        DB::table('user_access')->insert(['user_id' => $user->id,
            'user_type' => UserType::APP,
            'is_online' => true,
            'token' => $token]);

        return $this->withoutEncryptDataWithToken($user, $token);
    }

    public function logout(Request $request)
    {
        if (count($request->header('Authorization')) == 0) {
            return $this->withoutEncryptData(['message', 'Token não está presente']);
        }

        $token = str_replace('Bearer', '', $request->header('Authorization'));

        // save accesses
        DB::table('user_access')->where(['token' => $token])->update(['is_online' => false]);

        JWTAuth::invalidate($token);

        $this->withoutEncryptData(['message' => 'Usuário deslogado com sucesso']);
    }
}
