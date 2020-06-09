<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 15/04/2018
 * Time: 22:54
 */

namespace App\Http\Controllers;

use App\Course;
use App\Domain\TransationType;
use App\Transaction;
use App\User;
use App\UserCouses;
use App\UserShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    protected function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'initialDate' => 'required',
            'finalDate' => 'required',
            'shop_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $transations = DB::table('transactions')
            ->join('user_shop', 'user_shop.id', '=', 'transactions.user_shop_id')
            ->whereBetween('transactions.created_at', function ($q) use ($request) {
                return [$request['initialDate'], 'finalDate'];
            })
            ->where(['user_shop.shop_id' => $request['shop_id'], 'transactions.is_transactions_in_stars' => false])
            ->select('transaction.value', 'created_at')
            ->orderBy('created_at')
            ->get();

        $totalValue = 0;
        $input = 0;
        $output = 0;

        $response = new \stdClass();

        foreach ($transations as $transaction) {

            if ($transaction->transation_type == TransationType::INPUT) {
                $input += $input;
                $totalValue += $transaction->value;
            } else {
                $totalValue -= $transaction->value;
                $output += $transaction->value;
            }
        }

        $response->total = $totalValue;
        $response->input = $input;
        $response->output = $output;

        return $this->withoutEncryptData($response);
    }
}