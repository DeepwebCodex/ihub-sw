<?php

/**
 * Drive Media Igrosoft
 */

Route::post('/', "DriveMediaIgrosoftController@index");
Route::any('{any}', "DriveMediaIgrosoftController@error");

