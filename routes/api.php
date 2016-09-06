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
Route::post('casino', "CasinoController@index");
Route::post('casino/auth', "CasinoController@auth");
Route::post('casino/getbalance', "CasinoController@getbalance");
Route::post('casino/refreshtoken', "CasinoController@refreshtoken");
Route::post('casino/payin', "CasinoController@payin");
Route::post('casino/payout', "CasinoController@payout");
Route::post('casino/gen_token', "CasinoController@gen_token");