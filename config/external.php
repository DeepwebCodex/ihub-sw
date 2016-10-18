<?php

return [

    'api' => [

        'account_roh' => [
            'host'  => env('API_ACCOUNT_ROH_HOST', 'de2ef01d.dev.favorit'),
            'port'  => env('API_ACCOUNT_ROH_PORT', 7768)
        ],

        'account_session' => [
            'host'  => env('API_ACCOUNT_SESSION_HOST', 'de2ef01d.dev.favorit'),
            'port'  => env('API_ACCOUNT_SESSION_PORT', 8061)
        ],

        'account_op' => [
            'host'  => env('API_ACCOUNT_OP_HOST', 'de2ef01d.dev.favorit'),
            'port'  => env('API_ACCOUNT_OP_PORT', 8008)
        ],

        'cards_roh' => [
            'host'  => env('API_CARDS_ROH_HOST', 'de2ef01d.dev.favorit'),
            'port'  => env('API_CARDS_ROH_PORT', 7767)
        ],

        'config_agent_roh' => [
            'host'  => env('API_CONFIGAGENT_ROH_HOST', 'de2ef01d.dev.favorit'),
            'port'  => env('API_CONFIGAGENT_ROH_PORT', 7767)
        ],

        'cash_desk_roh' => [
            'host'  => env('API_CASH_DESK_ROH_HOST', 'de2ef01d.dev.favorit'),
            'port'  => env('API_CASH_DESK_ROH_PORT', 7763)
        ]
    ],

    'hazlecast_sessions' => [
        'host'  => env('API_HAZLE_SESSIONS_HOST', 'de2ef01d.dev.favorit'),
        'port'  => env('API_HAZLE_SESSIONS_PORT', 5701)
    ]

];