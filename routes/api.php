<?php

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
    Route::post('auth', "CasinoController@auth");
    Route::post('getbalance', "CasinoController@getBalance");
    Route::post('refreshtoken', "CasinoController@refreshToken");
    Route::post('payin', "CasinoController@payIn");
    Route::post('payout', "CasinoController@payOut");
    Route::post('gen_token', "CasinoController@genToken");
    Route::any('{any}', "CasinoController@error");
});

/**EuroGamesTech controller routes */
Route::group(['prefix' => 'egt'], function () {
    Route::post('Authenticate', "EuroGamesTechController@authenticate");
    Route::post('Withdraw', "EuroGamesTechController@withdraw");
    Route::post('Deposit', "EuroGamesTechController@deposit");
    Route::post('WithdrawAndDeposit', "EuroGamesTechController@withdrawAndDeposit");
    Route::post('GetPlayerBalance', "EuroGamesTechController@getPlayerBalance");
    Route::any('{any}', "EuroGamesTechController@error");
});