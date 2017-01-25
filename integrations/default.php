<?php

return [
    'casino' => [
        'service_id'    => 28,
        'secret_word'   => 'ererr4SD',
    ],

    'egt' => [
        'service_id'    => 27,
        'UserName'      => 'FavbetEGTSeamless',
        'Password'      => '6IQLjj8Jowe3X',
        'secret'        => 'Qasdf3QEFDDCS2'
    ],

    'microgaming' => [
        'service_id'            => 17,
        'login_server'          => 'microgaming',
        'password_server'       => 'hawai',
        'use_secure_request'    => false,
        'list_currency'         => ['WMZ' => 'USD', 'WMR' => 'RUB', 'WMU' => 'UAH', 'WME' => 'EUR']
    ],

    'virtualBoxing' => [
        'service_id' => 23,
        'sport_id'  => 100,
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
        ]
    ],

    'inspired' => [
        'sport_id'      => 86,
        'country_id'    => 10,
        'gender'        => 'mixed',
        'user_id'       => 155,
        'amqp'          => [
            'exchange' => 'ivgbet',
            'key'      => 'ivgbet'
        ],
        'allowed_ips'   => [ '10.1.4.47', '10.1.25.28', '185.16.228.226' ],
        'service_id'    => 2,
        'block_ips'     => false,
        'market'        => [
            'result_type_id' => 1
        ],
        //Horses = 0, Dogs = 1, Speedway = 2, F1Cars = 3, Football = 4, Numbers = 5, Trotting = 6, Cycling = 7 and Tennis = 8
        //category_id => ControllerId => some_id ?
        'sports' => [
            0 => [
                'name' => 'Horses',
                'game_result_scope_id' => 11,
                'category_id' => [ 101 => 1685, 102 => 1667, 103 => 1676,  104 => 3320, 105 => 1554, 106 => 3334 ],
                'sportform_prebet_id' => 168,
                'sportform_live_id' => 167,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'racer' => [1119, 1122, 1116]
                ]
            ],
            1 => [
                'name'  => 'Dogs',
                'game_result_scope_id' => 11,
                'category_id' => [ 101 => 1687, 102 => 1669, 103 => 1678, 104 => 3327, 105 => 1556, 106 => 3335 ],
                'sportform_prebet_id' => 164,
                'sportform_live_id' => 163,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'forecastData' => [ 1049 ],
                    'racer' => [ 1116, 1119, 1122, 1124, 1125, 1126 ],
                    'winnerOddEven' => [ 991 ],
                    'winnerOneOfTwo' => [ 1048 ],
                    'winnerYesNo' => [ 1121 ]
                ]
            ],
            2 => [
                'name' => 'Speedway',
                'game_result_scope_id' => 11,
                'category_id' => [ 101 => 1688, 102 => 1670, 103 => 1679, 104 => 3328, 105 => 1557, 106 => 3336 ],
                'sportform_prebet_id' => 174,
                'sportform_live_id' => 173,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'racer' => [ 1116, 1119, 1122 ]
                ]
            ],
            3 => [
                'name' => 'F1Cars',
                'game_result_scope_id' => 11,
                'category_id' => [ 101 => 1689, 102 => 1671, 103 => 1680, 104 => 3329, 105 => 1558, 106 => 3337 ],
                'sportform_prebet_id' => 166,
                'sportform_live_id' => 165,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'racer' => [ 1116, 1119, 1122 ]
                ]
            ],
            4 => [
                'name'  => 'Soccer',
                'game_result_scope_id' => 1,
                'category_id' => [ 101 => 1690, 102 => 1672, 103 => 1681, 104 => 2982, 105 => 1559, 106 => 3338 ],
                'sportform_prebet_id' => 172,
                'sportform_live_id' => 171,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'doubleChances' => [ 40 ],
                    'europeanHandicaps' => [ 30 ],
                    'goals' => [ 1111 ],
                    'overUnders' => [ 50 ],
                    'scores' => [ 1110 ],
                    'wdls' => [ 1 ]
                ]
            ],
            5 => [
                'name'  => 'Numbers',
                'game_result_scope_id' => 66,
                'category_id' => [ 101 => 1691, 102 => 1673, 103 => 1682, 104 => 3330, 105 => 1560, 106 => 3339 ],
                'sportform_prebet_id' => 170,
                'sportform_live_id' => 169,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    null
                ]
            ],
            6 => [
                'name' => 'Trotting',
                'game_result_scope_id' => 11,
                'category_id' => [ 101 => 1692, 102 => 1674, 103 => 1683, 104 => 3331, 105 => 1561, 106 => 3340 ],
                'sportform_prebet_id' => 178,
                'sportform_live_id' => 177,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'racer' => [ 1116, 1119, 1122 ]
                ]
            ],
            7 => [
                'name' => 'Cycling',
                'game_result_scope_id' => 11,
                'category_id' => [ 101 => 1693, 102 => 1675, 103 => 1684, 104 => 3332, 105 => 1562, 106 => 3341 ],
                'sportform_prebet_id' => 162,
                'sportform_live_id' => 161,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'racer' => [ 1116, 1119, 1122 ]
                ]
            ],
            8 => [
                'name' => 'Tennis',
                'game_result_scope_id' => 1,
                'category_id' => [ 101 => 1686, 102 => 1668, 103 => 1677, 104 => 3333, 105 => 1555, 106 => 3278],
                'sportform_prebet_id' => 176,
                'sportform_live_id' => 175,
                'max_bet' => 10000,
                'max_payout' => 10000,
                'stop_loss' => 1000000,
                'markets' => [
                    'PlayerWinOutcomes' => [ 5 ],
                    'ScoreBetOutcomes' => [ 1115 ],
                    'TotalPointsOutcomes' => [ 1112 ]
                ]
            ]
        ]
    ],

    'betGames' => [
        'service_id' => 13,
        'token_expiration_time' => 1,
        'secret' => 'dfFWgbF3r4efr',
    ],
];