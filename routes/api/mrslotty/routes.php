<?php

Route::any('{any}', "MrSlottyController@error");
Route::any('/', "MrSlottyController@index");
