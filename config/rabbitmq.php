<?php

return [
    'driver' => 'rabbitmq',
    'host' => env('RABBITMQ_HOST'),
    'port' => env('RABBITMQ_PORT'),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'login' => env('RABBITMQ_USER'),
    'password' => env('RABBITMQ_PASS'),
    // name of the default queue,
    'queue' => env('RABBITMQ_QUEUE'),
    // create the exchange if not exists
    'exchange_declare' => env('RABBITMQ_EXCHANGE_DECLARE', true),
    // create the queue if not exists and bind to the exchange
    'queue_declare_bind' => env('RABBITMQ_QUEUE_DECLARE_BIND', true),
    'queue_params' => [
        'passive' => env('RABBITMQ_QUEUE_PASSIVE', false),
        'durable' => env('RABBITMQ_QUEUE_DURABLE', true),
        'exclusive' => env('RABBITMQ_QUEUE_EXCLUSIVE', false),
        'auto_delete' => env('RABBITMQ_QUEUE_AUTODELETE', false),
    ],
    'exchange_params' => [
        'name' => env('RABBITMQ_EXCHANGE_NAME', null),
        // more info at http://www.rabbitmq.com/tutorials/amqp-concepts.html
        'type' => env('RABBITMQ_EXCHANGE_TYPE', 'direct'),
        'passive' => env('RABBITMQ_EXCHANGE_PASSIVE', false),
        // the exchange will survive server restarts
        'durable' => env('RABBITMQ_EXCHANGE_DURABLE', true),
        'auto_delete' => env('RABBITMQ_EXCHANGE_AUTODELETE', false),
    ],
    // the number of seconds to sleep if there's an error communicating with rabbitmq
    'sleep_on_error' => env('RABBITMQ_ERROR_SLEEP', 5),
    'connect_timeout' => env('RABBITMQ_CONNECTION_TIMEOUT', 5),
];
