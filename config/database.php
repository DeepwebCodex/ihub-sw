<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_OBJ,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'erlybet'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'account' => [
            'driver' => 'pgsql',
            'host' => env('DB_ACCOUNT_HOST', 'db01dev.favorit'),
            'port' => env('DB_ACCOUNT_PORT', '5432'),
            'database' => env('DB_ACCOUNT_DATABASE', 'account'),
            'username' => env('DB_ACCOUNT_USERNAME', 'g_develop'),
            'password' => env('DB_ACCOUNT_PASSWORD', 'hb8g7t34fbv09to'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'account',
            'sslmode' => 'prefer',
        ],
        'erlybet' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST_ERLYBET', 'de2db02d.dev.favorit'),
            'port' => env('DB_PORT_ERLYBET', '5432'),
            'database' => env('DB_DATABASE_ERLYBET', 'erlybet'),
            'username' => env('DB_USERNAME_ERLYBET', 'g_develop'),
            'password' => env('DB_PASSWORD_ERLYBET', 'hb8g7t34fbv09to'),
            'charset' => 'utf8',
            'prefix' => '',
            'sslmode' => 'prefer',
        ],
        'integration' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'db01dev.favorit'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'erlybet'),
            'username' => env('DB_USERNAME', 'g_develop'),
            'password' => env('DB_PASSWORD', 'hb8g7t34fbv09to'),
            'charset' => 'utf8',
            'prefix' => '',
            'sslmode' => 'prefer',
            'schema' => 'integration',
        ],
        'erlybet_slave' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'db01dev.favorit'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_ERLYBET_SLAVE', 'erlybet_slave'),
            'username' => env('DB_USERNAME', 'g_develop'),
            'password' => env('DB_PASSWORD', 'hb8g7t34fbv09to'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'line' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST_LINE', 'de2db02d.dev.favorit'),
            'port' => env('DB_PORT_LINE', '5432'),
            'database' => 'line',
            'username' => env('DB_USERNAME_LINE', 'g_develop'),
            'password' => env('DB_PASSWORD_LINE', 'hb8g7t34fbv09t'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'trans' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST_LINE', 'de2db02d.dev.favorit'),
            'port' => env('DB_PORT_LINE', '5432'),
            'database' => 'trans',
            'username' => env('DB_USERNAME_LINE', 'g_develop'),
            'password' => env('DB_PASSWORD_LINE', 'hb8g7t34fbv09t'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ]

    ],

];
