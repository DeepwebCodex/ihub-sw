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

/**Casino controller routes */
Route::group(['prefix' => 'casino'], function () {
    Route::get('/', "CasinoController@index"); // random test route
    Route::post('auth', "CasinoController@auth");
    Route::post('getbalance', "CasinoController@getBalance");
    Route::post('refreshtoken', "CasinoController@refreshToken");
    Route::post('payin', "CasinoController@payIn");
    Route::post('payout', "CasinoController@payOut");
    Route::post('gen_token', "CasinoController@genToken");
    Route::any('{any}', "CasinoController@error");
});