<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 14/03/2018
 * Time: 14:32
 */

namespace App\Http\Controllers;


use App\City;
use App\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class LocationController extends Controller
{

    protected function cities(Request $request)
    {
        return City::where('state_id', $request["stateID"])->get();
    }

    protected function states(Request $request)
    {
        return State::all();
    }

    protected function searchByCep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cep' => 'required|size:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }


        $cep = $request['cep'];


        $url = "https://viacep.com.br/ws/{$cep}/json/";

        if ($this->get_http_response_code($url) != '200') {

            $response = [
                'message' => "Cep não encontrado!"
            ];

            return $this->withoutEncryptData($response);
        }

        $response = file_get_contents("https://viacep.com.br/ws/{$cep}/json/");
        header("Content-type: application/json");
        echo $response;
    }

    function get_http_response_code($url)
    {
        $headers = get_headers($url);
        $code_response = substr($headers[0], 9, 3);
        return $code_response;
    }

}