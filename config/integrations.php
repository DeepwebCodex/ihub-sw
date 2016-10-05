<?php

return [

    'casino' => [
        'secret_word'   => 'ererr4SD',
        'service_id'    => 28
    ],

    'egt' => [
        'service_id'    => 27,
        'UserName'      => 'FavbetEGTSeamless',
        'Password'      => '6IQLjj8Jowe3X',
        'secret'        => 'Qasdf3QEFDDCS2'
    ],

    'goldenRace' => [
        'partners_id_by_country' => [
            'ua' => [1, 18],
            'hr' => [50]
        ]
    ],

    'microGaming' => [
        'service_id'        => 17,
        'login_server'      => 'microgaming',
        'password_server'   => 'hawai',
        'time_expire'       => 300,
        'list_currency'     => ['WMZ' => 'USD', 'WMR' => 'RUB', 'WMU' => 'UAH', 'WME' => 'EUR'],
        'jackpot_url'       => 'http://www.quickfiregames.co.uk/services/tickers/progressivesxml.asmx/Games',
        'jackpot_set_url'   => '/microgaming/set_jackpot', // +_+
        'security_word'     => 'sdfw#4,.WWsdfdf',   // WTF ?
        'csid'              => 1115 // WTF #2
    ],

    'virtualBoxing' => [
        'service_id' => 23,
        'sport_id'  => 100,
        'weigh' => 100,
        'amqp_exchange' => 'service_23',
        'amqp_key' => 'service_23.event.',
        'country_id' => 267,
        'enet_import' => 'undefined',
        'import_odds_provider' => null,
        'max_bet' => 10000.00,
        'max_payout' => 10000.00,
        'stop_loss' => 1000000.00,
        'margin' => 108.00,
        'margin_prebet' => 108.00,
        'gender' => 'mixed',
        'user_id' => 155,
        'sport_union_id' => null,
        'stop_loss_exp' => 1000000.00,
        'max_bet_live' => null,
        'max_payout_live' => null,
        'info_url' => NULL
    ]
];