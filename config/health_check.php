<?php
return [
    'postgresql_db_check' => [
        'name' => 'PostgreSQL database connections check',
        'checker' => \iHubGrid\HealthCheck\Components\Checkers\DatabaseChecker::class
    ],
];