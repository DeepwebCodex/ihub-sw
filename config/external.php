<?php

return [

    'api' => [
        'amqp' => [
            'host' => env('API_AMQP_HOST', 'de2ef01d.dev.favorit'),
            'port' => env('API_AMQP_PORT', 10102),
        ],

        'amqp_old' => [
            'host' => env('API_AMQP_HOST', 'de2of01d.dev.favorit'),
            'port' => env('API_AMQP_PORT', 8001),
        ],

        'mysterion' => [
            'is_enabled' => env('API_MYSTERION_IS_ENABLED', false),
            'host' => env('API_MYSTERION_HOST'),
            'port' => env('API_MYSTERION_PORT'),
            'action' => env('API_MYSTERION_ACTION'),
            'sid' => env('API_MYSTERION_SID'),
            'skey' => env('API_MYSTERION_SKEY'),
        ]
    ],

    'hazlecast_sessions' => [
        'host'  => env('API_HAZLE_SESSIONS_HOST', 'de2ef01d.dev.favorit'),
        'port'  => env('API_HAZLE_SESSIONS_PORT', 5701)
    ]

];