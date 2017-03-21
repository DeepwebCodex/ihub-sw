<?php

Route::post('/', "DriveMediaNovomaticDeluxeController@index");
Route::any('{any}', "DriveMediaNovomaticDeluxeController@error");
