<?php

Route::post('/', "BetGamesController@index");
Route::post('/favbet', "BetGamesController@index");
Route::post('/favbet-app', "BetGamesController@index");
Route::post('/favorit', "BetGamesController@index");
Route::post('/favorit-app', "BetGamesController@index");
