<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Rotas usuário deslogado */

// Rotas de autenticação
Route::post('auth/sign_in', array('uses' => 'Auth\LoginController@loginFranchiseAdmin'));

/* Rotas usuário logado */
Route::middleware(['jwt.auth'])->group(function () {
    Route::delete('auth/logout', array('uses' => 'Auth\LoginController@logout'));
    Route::post('client/index', array('uses' => 'ClientController@byID'));
    Route::post('client/create', array('uses' => 'ClientController@register'));
    //here
    Route::post('client/profile', array('uses' => 'ClientController@getProfile'));
    Route::post('client/transaction', array('uses' => 'ClientController@getTransactions'));
    Route::post('client/stars', array('uses' => 'ClientController@getStars'));
    Route::post('client/give_stars', array('uses' => 'ClientController@giveStars'));
//    Route::post('client/convert_stars', array('uses' => 'ClientController@convertStars'));
    Route::post('client/convert_credit', array('uses' => 'ClientController@convertInCredits'));
    Route::post('client/give_credit', array('uses' => 'ClientController@giveCredit'));
    Route::post('client/remove_credit', array('uses' => 'ClientController@creditOutflow'));
    Route::post('client/give_babycoin', array('uses' => 'ClientController@giveBabyCoin'));
    Route::post('client/remove_babycoin', array('uses' => 'ClientController@removeBabyCoin'));
    Route::post('news/all', array('uses' => 'NewsController@getNewsByShop'));
    Route::post('news/create', array('uses' => 'NewsController@create'));
    Route::post('products/evaluate/all', array('uses' => 'ProductEvaluateController@getByShop'));
    Route::post('products/evaluate/refuse', array('uses' => 'ProductEvaluateController@refuseProduct'));
    Route::post('products/evaluate/accept', array('uses' => 'ProductEvaluateController@evaluate'));
});
