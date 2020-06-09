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
Route::post('auth/sign_in', array('uses' => 'Auth\LoginController@loginGeneralAdmin'));
Route::get('cep/search', array('uses' => 'LocationController@searchByCep'));

/* Rotas usuário logado */
Route::middleware(['jwt.auth'])->group(function () {
    Route::delete('auth/logout', array('uses' => 'Auth\LoginController@logout'));
    Route::get('dashboard', array('uses' => 'DashboardController@dashboard'));
    Route::post('shop/billing', array('uses' => 'ShopController@billing'));
    Route::post('shop/create', array('uses' => 'ShopController@create'));
    Route::post('report/show', array('uses' => 'Reportcontroller@index'));
    Route::post('client/all', array('uses' => 'ClientController@all'));

    // new
    Route::post('shop/create', array('uses' => 'ShopController@create'));
    Route::post('client/index', array('uses' => 'ClientController@byID'));
    Route::post('client/detail', array('uses' => 'ClientController@detail'));
    Route::get('news/all', array('uses' => 'NewsController@all'));
    Route::post('news/create', array('uses' => 'NewsController@create'));
    Route::get('course/all', array('uses' => 'CourseController@all'));
    Route::post('course/edit', array('uses' => 'CourseController@edit'));
    Route::post('course/enable_or_disable', array('uses' => 'CourseController@enable'));
    Route::post('course/create', array('uses' => 'CourseController@create'));
    Route::post('report', array('uses' => 'ReportController@show'));
});
