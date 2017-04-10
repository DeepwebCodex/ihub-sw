<?php

Route::any('{any}', "WirexGamingController@error");
Route::post('/', "WirexGamingController@index");
