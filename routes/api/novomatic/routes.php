<?php

Route::post('/', "NovomaticController@index");
Route::any('{any}', "NovomaticController@error");
