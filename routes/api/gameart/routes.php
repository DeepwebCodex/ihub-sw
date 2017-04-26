<?php

Route::get('/', "GameArtController@index");
Route::any('{any}', "GameArtController@error");
