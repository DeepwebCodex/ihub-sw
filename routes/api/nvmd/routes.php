<?php

Route::post('/', "NovomaticDeluxeController@index");
Route::any('{any}', "NovomaticDeluxeController@error");
