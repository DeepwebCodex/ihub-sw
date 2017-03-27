<?php

Route::any('{any}', "VirtualBoxController@error");
Route::post('/', "VirtualBoxController@index");
