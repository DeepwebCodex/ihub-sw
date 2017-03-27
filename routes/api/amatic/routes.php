<?php

/**
 * DriveMedia Amatic
 */

Route::post('/', "DriveMediaAmaticController@index");
Route::any('{any}', "DriveMediaAmaticController@error");

