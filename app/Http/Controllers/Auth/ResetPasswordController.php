<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email, 'test' => '']
        );
    }

    public function broker()
    {
        return Password::broker();
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param  string $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {

        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [

            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
    }

    public function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        $textResponse = '';

        if ($response == Password::PASSWORD_RESET) {
            return view('auth.passwords.successful_reset')->with(
                ['email' => $request['email']]);
        } else if ($response == Password::INVALID_TOKEN) {

            $textResponse = 'Token de autenticação inválido';
        } else if ($response == Password::INVALID_USER) {

            $textResponse = 'Usuário inválido';
        } else if ($response == Password::INVALID_PASSWORD) {

            $textResponse = 'Senha inválida';
        }


        return view('auth.passwords.reset')->with(
            ['test' => $textResponse,
                'token' => $request['token'],
                'email' => $request['email']]);

    }

    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }

    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }
}
