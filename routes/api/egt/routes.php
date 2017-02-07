<?php

Route::post('Authenticate', "EuroGamesTechController@authenticate");
Route::post('Withdraw', "EuroGamesTechController@withdraw");
Route::post('Deposit', "EuroGamesTechController@deposit");
Route::post('WithdrawAndDeposit', "EuroGamesTechController@withdrawAndDeposit");
Route::post('GetPlayerBalance', "EuroGamesTechController@getPlayerBalance");
Route::any('{any}', "EuroGamesTechController@error");
Route::any('/', "EuroGamesTechController@error");
