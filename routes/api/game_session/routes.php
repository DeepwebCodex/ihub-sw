<?php

Route::post('create', "GameSessionController@create");
Route::post('create_with_context', "GameSessionController@createWithContext");
Route::any('{any}', "GameSessionController@error");
