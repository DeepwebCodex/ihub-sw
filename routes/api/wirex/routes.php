<?php

Route::any('{any}', "WirexGamingController@error");
Route::get('/', "WirexGamingController@docs");
Route::post('/', "WirexGamingController@index");
