<?php

return [
    'enabled' => env('FINANCE_SERVICE_ENABLED', false),
    'service' => [
        'host' => env('FINANCE_SERVICE_HOST'),
        'port' => env('FINANCE_SERVICE_PORT')
    ],
    'services' => [
        13,21,27,37,46,48,49,61,62
    ]
];