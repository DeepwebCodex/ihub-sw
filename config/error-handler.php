<?php
return [
    'logger' => [
        'default' => env('LOG_DRIVER', null),
        'connections' => [
            'rabbit' => [
                'host' => env('LOG_RABBIT_HOST', false),
                'port' => env('LOG_RABBIT_PORT', false),
                'user' => env('LOG_RABBIT_USER', false),
                'password' => env('LOG_RABBIT_PASS', false),
                'prefix' => env('LOG_RABBIT_PREFIX', ""),
                'default_exchange' => 'log_exchange',
                'vhost' => env('LOG_RABBIT_VHOST', "/"),
                'queueList' => [
                    [
                        'event_levels' => ['error', 'critical', 'alert', 'emergency'],
                        'queue_params' => [
                            'queue' => 'errors',
                            'passive' => false,
                            'durable' => true,
                            'exclusive' => false
                        ]
                    ],
                    [
                        'event_levels' => ['warning'],
                        'queue_params' => [
                            'queue' => 'warnings',
                            'passive' => false,
                            'durable' => true,
                            'exclusive' => false
                        ]
                    ],
                    [
                        'event_levels' => ['notice', 'info'],
                        'queue_params' => [
                            'queue' => 'notices',
                            'passive' => false,
                            'durable' => true,
                            'exclusive' => false
                        ]
                    ]
                ]
            ],
            'file' => [
                'log' => env('APP_LOG', 'single'),
                'log_level' => env('APP_LOG_LEVEL', 'debug')
            ],
            'mongo' => [
                'server' => env('LOG_MONGO_SERVER'),
                'db_name' => env('LOG_MONGO_DB_NAME'),
                'collection_name' => env('LOG_MONGO_COLLECTION_NAME')
            ]
        ]
    ],
    'request-response-log' => [
        'status-codes' => null, // array if log only some
        'log-only-api-calls' => true
    ],
    'external-services-requests' => [
        'is_enabled' => env('LOG_EXTERNAL_REQUESTS', false),
        'exclude_filter' => ['Mysterion'],
    ],
    'trim-response' => env('LOG_TRIM_RESPONSE_SIZE', 255)
];