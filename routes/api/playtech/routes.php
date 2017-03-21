<?php

/**
 * Drive Media Playtech
 */

Route::post('/', "DriveMediaPlaytechController@index");
Route::any('{any}', "DriveMediaPlaytechController@error");
