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
Route::post('auth/sign_in', array('uses' => 'Auth\LoginController@loginApp'));
Route::post('auth/register', array('uses' => 'Auth\RegisterController@register'));

// Rotas de localização
Route::get('cities', array('uses' => 'LocationController@cities'));
Route::get('states', array('uses' => 'LocationController@states'));

// others
Route::post('auth/facebook/profile', array('uses' => 'Auth\RegisterController@getFacebookProfile'));
Route::post('password/forgot', array('uses' => 'Auth\ForgotPasswordController@sendEmail'));
Route::get('shop/all', array('uses' => 'ShopController@all'));


/* Rotas usuário logado */
Route::middleware(['jwt.auth'])->group(function () {

    Route::delete('auth/logout', array('uses' => 'Auth\LoginController@logout'));
    Route::post('home', array('uses' => 'HomeController@index'));
    Route::post('credit/show', array('uses' => 'HomeController@generateCredit'));
    Route::post('credit/accept', array('uses' => 'HomeController@acceptCredit'));
    Route::post('credit/reject', array('uses' => 'HomeController@refuseCredit'));
    Route::get('shops', array('uses' => 'ShopController@index'));
    Route::post('shop/detail', array('uses' => 'ShopController@show'));
    Route::post('shop/follow', array('uses' => 'ShopController@followShop'));
    Route::post('shop/link', array('uses' => 'ShopController@link'));
    Route::post('news', array('uses' => 'NewsController@show'));
    Route::post('news/like', array('uses' => 'NewsController@like'));
    Route::post('news/share', array('uses' => 'NewsController@share'));
    Route::post('courses', array('uses' => 'CourseController@show'));
    Route::post('courses/acquire', array('uses' => 'CourseController@acquire'));
    Route::post('courses/my_courses', array('uses' => 'CourseController@myCourses'));
    Route::post('products/evaluate/create', array('uses' => 'ProductEvaluateController@create'));
    Route::post('products/evaluate/show', array('uses' => 'ProductEvaluateController@show'));
    Route::post('products/evaluate/accept', array('uses' => 'ProductEvaluateController@accept'));
    Route::post('products/evaluate/reject', array('uses' => 'ProductEvaluateController@reject'));
    Route::get('faq/show', array('uses' => 'FaqController@show'));
});
