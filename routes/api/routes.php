<?php

Route::post('/ferapont/apiplace/egt/Authenticate', "EuroGamesTechController@authenticate");
Route::post('/ferapont/apiplace/egt/Withdraw', "EuroGamesTechController@withdraw");
Route::post('/ferapont/apiplace/egt/Deposit', "EuroGamesTechController@deposit");
Route::post('/ferapont/apiplace/egt/WithdrawAndDeposit', "EuroGamesTechController@withdrawAndDeposit");
Route::post('/ferapont/apiplace/egt/GetPlayerBalance', "EuroGamesTechController@getPlayerBalance");

Route::any('/ferapont/apiplace/egt/{any}', "EuroGamesTechController@error");
Route::any('/ferapont/apiplace/egt/', "EuroGamesTechController@error");
