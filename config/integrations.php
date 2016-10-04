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

    'microGaming' => [
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
    ]
];