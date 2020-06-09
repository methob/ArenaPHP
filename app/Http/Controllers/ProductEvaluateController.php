<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 23/03/2018
 * Time: 10:22
 */

namespace App\Http\Controllers;


use App\Domain\ProductsEvaluationStatus;
use App\Domain\TransationType;
use App\ProductsEvaluate;
use App\Transaction;
use App\UserCredit;
use App\UserCreditDeclined;
use App\UserShop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;

class ProductEvaluateController extends Controller
{

    private function validator($data)
    {
        return Validator::make($data, [
            'id' => 'required|exists:users',
            'shop_id' => 'required|exists:shop,id',
            'name' => 'required',
            'is_donate' => 'required'
        ]);
    }

    private function validateUser($data)
    {
        return Validator::make($data, [
            'id' => 'required|exists:users'
        ]);
    }

    private function validatorAcceptOrRefuse($data)
    {
        return Validator::make($data, [
            'product_id' => 'required|exists:products_evaluate,id',
            'id' => 'required|exists:users'
        ]);
    }

    protected function show(Request $request)
    {

        $validator = $this->validateUser($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->withoutEncryptData(ProductsEvaluate::where(['user_id' => $request['id']])->get());
    }

    protected function getByShop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->withoutEncryptData(ProductsEvaluate::where(['shop_id' => $request['shop_id'],
            'status' => ProductsEvaluationStatus::PENDING])->get());
    }

    protected function evaluate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_evaluate_id' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $product = ProductsEvaluate::find($request['product_evaluate_id']);

        $product->value = $request['value'];
        $product->status = ProductsEvaluationStatus::EVALUATED;
        $product->save();

        $response = [
            'message' => "Produto avaliado com sucesso!"
        ];

        return $this->withoutEncryptData($response);

    }

    protected function refuseProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_evaluate_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $product = ProductsEvaluate::find($request['product_evaluate_id']);
        $product->status = ProductsEvaluationStatus::REFUSED;
        $product->save();

        $response = [
            'message' => "Produto avaliado com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function create(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        ProductsEvaluate::create(['name' => $request['name'], 'user_id' => $request['id'],
            'shop_id' => $request['shop_id'], 'is_donate' => $request['is_donate'], 'code' => mt_rand(100000, 999999)]);

        $response = [
            'message' => "Avaliação criada com sucesso!"
        ];

        return $this->withoutEncryptData($response);
    }

    protected function accept(Request $request)
    {
        $validator = $this->validatorAcceptOrRefuse($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $product = ProductsEvaluate::find($request['product_id']);

        if ($product == null || $product->status != ProductsEvaluationStatus::EVALUATED) {

            return response()->json([
                'message' => 'Não é possível receber os créditos desse produto'], 422);

        } else {

            $userShop = UserShop::where(['user_id' => $request['id'], 'shop_id' => $product->shop_id])->first();
            $userShop->current_credit += $product->value;
            $userShop->save();

            UserCredit::where(['user_shop_id' => $userShop->id, 'is_declined' => false,
                'product_evaluate_id' => $request['product_id']])->delete();

            Transaction::create(['value' => $product->value, 'transation_type' => TransationType::INPUT,
                'user_shop_id' => $userShop->id]);

            $response = [
                'message' => 'Crédito resgatado com sucesso!'
            ];

            $product->status = ProductsEvaluationStatus::RESCUED;
            $product->save();

            DB::table('user_credit_acceptance')->create(['user_id' => $request['id'], 'is_accepted' => true]);

            return $this->withoutEncryptData($response);
        }
    }

    protected function reject(Request $request)
    {
        $validator = $this->validatorAcceptOrRefuse($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => trans('validation.validate_message'),
                'errors' => $validator->errors()
            ], 422);
        }

        $product = ProductsEvaluate::find($request['product_id']);

        if ($product == null || $product->status != ProductsEvaluationStatus::EVALUATED) {

            return response()->json([
                'message' => 'Não é possível receber os créditos desse produto'], 422);

        } else {

            $userShop = UserShop::where(['user_id' => $request['id'], 'shop_id' => $product->shop_id])->first();

            $userCredit = UserCredit::where(['user_shop_id' => $userShop->id, 'is_declined' => false,
                'product_evaluate_id' => $product->id])->first();

            $userCredit->is_declined = true;
            $userCredit->save();

            UserCreditDeclined::create(['message' => $request['message'],
                'answer_option' => $request['answerOption'],
                'user_credit' => $userCredit->id]);

            $product->status = ProductsEvaluationStatus::REFUSED;
            $product->save();

            DB::table('user_credit_acceptance')->create(['user_id' => $request['id'], 'is_accepted' => false]);


            $response = [
                'message' => 'Crédito recusado con sucesso!'
            ];

            return $this->withoutEncryptData($response);
        }
    }
}