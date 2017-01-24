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
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'casino'], function () {
    Route::post('auth', "CasinoController@auth");
    Route::post('getbalance', "CasinoController@getBalance");
    Route::post('refreshtoken', "CasinoController@refreshToken");
    Route::post('payin', "CasinoController@payIn");
    Route::post('payout', "CasinoController@payOut");
    Route::post('gen_token', "CasinoController@genToken");
    Route::any('{any}', "CasinoController@error");
    Route::any('/', "CasinoController@error");
});

/**EuroGamesTech controller routes */
Route::group(['prefix' => 'egt'], function () {
    Route::post('Authenticate', "EuroGamesTechController@authenticate");
    Route::post('Withdraw', "EuroGamesTechController@withdraw");
    Route::post('Deposit', "EuroGamesTechController@deposit");
    Route::post('WithdrawAndDeposit', "EuroGamesTechController@withdrawAndDeposit");
    Route::post('GetPlayerBalance', "EuroGamesTechController@getPlayerBalance");
    Route::any('{any}', "EuroGamesTechController@error");
    Route::any('/', "EuroGamesTechController@error");
});

/**MicroGaming controller routes */
Route::group(['prefix' => 'mg'], function () {
    Route::any('{any}', "MicroGamingController@error");
    Route::any('/', "MicroGamingController@index");
});

/**VirtualBoxing controller routes */
Route::group(['prefix' => 'vb'], function () {
    Route::any('{any}', "VirtualBoxingController@error");
    Route::post('/', "VirtualBoxingController@index");
});

/**VirtualBox controller routes */
Route::group(['prefix' => 'vb2'], function () {
    Route::any('{any}', "VirtualBoxController@error");
    Route::post('/', "VirtualBoxController@index");
});

/**GameSession controller routes */
Route::group(['prefix' => 'game_session'], function () {
    Route::post('create', "GameSessionController@create");
    Route::any('{any}', "GameSessionController@error");
});

/** BetGames controller routes
 * @see App\Http\Controllers\Api\BetGamesController::index,
 * @see App\Http\Controllers\Api\BetGamesController::ping,
 * @see App\Http\Controllers\Api\BetGamesController::account,
 * @see App\Http\Controllers\Api\BetGamesController::getBalance,
 * @see App\Http\Controllers\Api\BetGamesController::refreshToken,
 * @see App\Http\Controllers\Api\BetGamesController::newToken,
 * @see App\Http\Controllers\Api\BetGamesController::bet,
 * @see App\Http\Controllers\Api\BetGamesController::win,
 */
Route::group(['prefix' => 'bg'], function () {
    Route::post('/', "BetGamesController@index");
});

Route::group(['prefix' => 'ivg'], function () {
    Route::post('/', "InspiredVirtualGaming@index");
});