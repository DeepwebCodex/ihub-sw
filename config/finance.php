<?php

return [
    'enabled' => env('FINANCE_SERVICE_ENABLED', false),
    'service' => [
        'host' => env('FINANCE_SERVICE_HOST'),
        'port' => env('FINANCE_SERVICE_PORT')
    ],
    'services' => [
        27,13,48,21
    ]
];