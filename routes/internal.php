<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'internal'], function () {
    Route::get('ivg/cancel_event/{limit?}/{type?}/{categoryId?}', 'InspiredVirtualGamingController@cancelEvent');

    Route::group(['prefix' => 'bg'], function () {
        Route::get('cashdeskCard', 'BetGamesController@cashdeskCard');
        Route::get('cashdeskCards', 'BetGamesController@cashdeskCards');
    });

    Route::group(['prefix' => 'gr'], function () {
        Route::get('get_report', 'GoldenRaceController@getReport');
        Route::get('get_report_cashdesk', 'GoldenRaceController@getReportCashdesk');
        Route::get('get_card_cashdesk', 'GoldenRaceController@getCardCashdesk');
    });

    Route::get('ld', 'LiveDealerController@checkTransactions');

    Route::group(['prefix' => 'games'], function () {
        Route::any('allgametypes/{lang?}', 'CasinoController@allGameTypes');
        Route::any('allproviders', 'CasinoController@allProviders');
        Route::any('game/{gameType?}/{gameUrl?}/{lang?}/{isMobile?}/{isDemo?}/', 'CasinoController@game');
        Route::any('allgames/{provider?}/{gameType?}/{lang?}', 'CasinoController@allGames');
        Route::any('allseo/{typeEntity?}/{entityName?}/{lang?}', 'CasinoController@allSeo');
    });
});
