<?php

Route::post('create', "GameSessionController@create");
Route::any('{any}', "GameSessionController@error");
