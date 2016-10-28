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
        'secret'        => 'Qasdf3QEFDDCS2',
        'game_real_url' => 'http://mgs-staging.egtmgs.com:8080/core-web-war/MGL',
        'game_demo_url' => 'https://free.egtmgs.com/favbet.php',
    ],

    'goldenRace' => [
        'partners_id_by_country' => [
            'ua' => [1, 18],
            'hr' => [50]
        ]
    ],

    'microgaming' => [
        'service_id'            => 17,
        'login_server'          => 'microgaming',
        'password_server'       => 'hawai',
        'time_expire'           => 300,
        'list_currency'         => ['WMZ' => 'USD', 'WMR' => 'RUB', 'WMU' => 'UAH', 'WME' => 'EUR'],
        'jackpot_url'           => 'http://www.quickfiregames.co.uk/services/tickers/progressivesxml.asmx/Games',
        'jackpot_set_url'       => '/microgaming/set_jackpot', // +_+
        'game_mobile_lobby_url' => 'https://www.quickfiregames.co.uk/mobile/menu.aspx?',
        'game_mobile_url'       => 'https://mobile3.gameassists.co.uk/MobileWebServices/casino/game/launch/mgs',
        'game_lobby_url'        => 'https://quickfire3.gameassists.co.uk/quickfiressl',
        'game_url'              => 'https://redirector3.valueactive.eu/Casino/Default.aspx',
        'security_word'         => 'sdfw#4,.WWsdfdf',   // WTF ?
        'csid'                  => 1115, // WTF #2
        'client_id'             => 40300,
        'server_id'             => 1866,
        'demo_game_client_id'   => 10001,
    ],

    'virtualBoxing' => [
        'service_id' => 23,
        'sport_id'  => 100,
        'amqp' => [
            'exchange' => 'service_23',
            'key' => 'service_23.event.'
        ],
        'country_id' => 267,
        'enet_import' => 'undefined',
        'import_odds_provider' => null,
        'stop_loss' => 1000000.00,
        'gender' => 'mixed',
        'user_id' => 155,
        'sport_union_id' => null,
        'stop_loss_exp' => 1000000.00,
        'max_bet_live' => null,
        'max_payout_live' => null,
        'info_url' => null,
        'rounds_map' => [1 => 31, 2 => 32, 3 => 33, 4 => 34, 5 => 35, 6 => 36],
        'scope_type' => ['point' => 1, 'knockdown' => 33, 'winner' => 12],
        'event_type' => 'prebet',
        'locked' => 'no',
        'weigh' => 100,
        'del' => 'no',
        'max_bet' => 10000.00,
        'max_payout' => 10000.00,
        'margin' => 108.00,
        'margin_prebet' => 108.00,
        'market' => [
            'OW' => 1,
            'OW_result_type' => 1,
            'max_bet' => 10000.00,
            'max_payout' => 10000.00,
            'stop_loss' => 1000000.00,
            'OW_weight' => 100,
            'match_result_type_id' => 1,
            'CS' => 1318,
            'CSR' => 1319,
            'T65' => 50,
            'OE' => 74,
            'KO1' => 1314,
            'KO2' => 1315,
            'KO3' => 1316,
            'KO4' => 1317,
        ],
    ],

    'live_dealer' => [
        'system_id' => '999',
        'server_addr' => '0.0.0.0',
        'api_url' => 'https://apitest.fundist.org/',
        'api_key' => '19d47072a7e46e82ce07b7d8b843cecc',
        'api_password' => '4575885165224895',
    ],
];