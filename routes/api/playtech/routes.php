<?php

Route::post('/', "PlaytechController@index");
Route::any('{any}', "PlaytechController@error");
