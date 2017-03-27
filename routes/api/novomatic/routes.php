<?php

Route::post('/', "DriveMediaNovomaticController@index");
Route::any('{any}', "DriveMediaNovomaticController@error");
