<?php

Route::any('{any}', "VirtualBoxingController@error");
Route::post('/', "VirtualBoxingController@index");
