<?php

Route::post('/', "DriveCasinoController@index");
Route::any('{any}', "DriveCasinoController@error");

