<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function withoutEncryptData($data)
    {
        return response()->json(['data' => $data], 200);
    }

    protected function withoutEncryptDataWithToken($data, $token)
    {
        return response()->json(['data' => $data], 200
        )->header('Authorization', "Bearer{$token}");
    }

    protected function encryptData($data)
    {
        return response()->json(['data' => encrypt($data)], 200);
    }


    protected function encryptDataWithToken($data, $token)
    {
        return response()->json(['data' => encrypt($data)], 200
        )->header('Authorization', "Bearer{$token}");
    }
}
