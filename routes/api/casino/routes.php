<?php

Route::post('auth', "CasinoController@auth");
Route::post('getbalance', "CasinoController@getBalance");
Route::post('refreshtoken', "CasinoController@refreshToken");
Route::post('payin', "CasinoController@payIn");
Route::post('payout', "CasinoController@payOut");
Route::post('gen_token', "CasinoController@genToken");
Route::any('{any}', "CasinoController@error");
Route::any('/', "CasinoController@error");
