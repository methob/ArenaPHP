<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function validateEmail($data)
    {
        return Validator::make($data, [
            'email' => 'required|string|email|max:255|exists:users,email',
        ]);
    }

    public function sendEmail(Request $request)
    {
        $validator = $this->validateEmail($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $this->broker()->sendResetLink($request->only('email'));

        $response = ["message" =>
            'um email foi enviado para sua você, verifique sua caixa de entrada para alterar sua senha.'];

        return $this->withoutEncryptData($response);
    }

    public function broker()
    {
        return Password::broker();
    }
}
