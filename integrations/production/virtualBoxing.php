<?php

return [
    'service_id' => 23,
    'sport_id' => 100,
    'amqp' => [
        'exchange' => 'service_23',
        'key' => 'service_23.event.'
    ],
    'country_id' => 267,
    'gender' => 'mixed',
    'user_id' => 155,
    'rounds_map' => [1 => 31, 2 => 32, 3 => 33, 4 => 34, 5 => 35, 6 => 36],
    'scope_type' => ['point' => 1, 'knockdown' => 33, 'winner' => 12],
    'max_bet' => 10000.00,
    'max_payout' => 10000.00,
    'stop_loss' => 1000000.00,
    'market' => [
        'result_type_id' => 1
    ],
    'sports' => [
        'box' => [
            'max_bet' => 10000,
            'max_payout' => 10000,
            'stop_loss' => 1000000,
            'game_result_scope_id' => '',
            'markets' => [
                'OW' => [1],
                'CS' => [1318],
                'CSR' => [1319],
                'T65' => [50],
                'OE' => [74],
                'KO1' => [1314],
                'KO2' => [1315],
                'KO3' => [1316],
                'KO4' => [1317]
            ]
        ]
    ],
    'allowed_ips' => [],
];
