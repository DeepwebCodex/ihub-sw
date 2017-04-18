<?php

Route::get('session', "EndorphinaController@session");
Route::get('balance', "EndorphinaController@balance");
Route::post('bet', "EndorphinaController@bet");
Route::post('win', "EndorphinaController@win");
Route::post('refund', "EndorphinaController@refund");
Route::any('{any}', "EndorphinaController@error");
