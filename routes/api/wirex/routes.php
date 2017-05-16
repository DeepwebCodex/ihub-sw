<?php

Route::any('{any}', "WirexGamingController@error");
Route::get('/', "WirexGamingController@wsdl");
Route::post('/', "WirexGamingController@index")->middleware('input.xml');
