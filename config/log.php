<?php

return [
    'mongo' => [
        'server' => env('LOG_MONGO_SERVER'),
        'db_name' => env('LOG_MONGO_DB_NAME'),
        'collection_name' => env('LOG_MONGO_COLLECTION_NAME')
    ]
];
