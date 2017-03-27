<?php

/**
 * DriveMedia Aristocrat
 */

Route::post('/', "DriveMediaAristocratController@index");
Route::any('{any}', "DriveMediaAristocratController@error");

