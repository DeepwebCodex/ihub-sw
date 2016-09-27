<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'internal'], function () {
    Route::get('ivg/cancel_event/{limit?}/{type?}/{categoryId?}', 'IvgController@cancelEvent');

    Route::group(['prefix' => 'bg'], function () {
        Route::get('cashdeskCard', 'BetGamesController@cashdeskCard');
        Route::get('cashdeskCards', 'BetGamesController@cashdeskCards');
    });

    Route::group(['prefix' => 'gr'], function () {
        Route::get('get_report', 'GoldenRaceController@getReport');
        Route::get('get_report_cashdesk', 'GoldenRaceController@getReportCashdesk');
        Route::get('get_card_cashdesk', 'GoldenRaceController@getCardCashdesk');
    });

    Route::get('ld', 'LdController@checkTransactions');

    Route::group(['prefix' => 'games'], function () {
        Route::get('allgametypes/{lang?}', 'GamesController@allGameTypes');
        Route::get('allproviders', 'GamesController@allProviders');
        Route::get('game/{gameType?}/{gameName?}/{lang?}/{mobile?}/{demo?}/', 'GamesController@game');
        Route::get('allgames/{provider?}/{gameType?}/{lang?}', 'GamesController@allGames');
        Route::get('allseo/{typeEntity?}/{entityName?}/{lang?}', 'GamesController@allSeo');
    });
});
