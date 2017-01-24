<?php

return [
    'casino' => [
        'service_id'    => 28,
        'secret_word'   => 'ererr4SD',
    ],
    'egt' => [
        'service_id' => 27,
        'UserName' => 'FavbetEGTSeamless',
        'Password' => '6IQLjj8Jowe3X',
        'secret' => 'Qasdf3QEFDDCS2'
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
        'sport_id' => 100,
        'amqp' => [
            'exchange' => 'service_23',
            'key' => 'service_23.event.'
        ],
        'country_id' => 267,
        'enet_import' => 'undefined',
        'import_odds_provider' => null,
        'stop_loss' => 1000000.00,
        'gender' => 'mixed',
        'user_id' => 155,
        'sport_union_id' => null,
        'stop_loss_exp' => 1000000.00,
        'max_bet_live' => null,
        'max_payout_live' => null,
        'info_url' => null,
        'rounds_map' => [1 => 31, 2 => 32, 3 => 33, 4 => 34, 5 => 35, 6 => 36],
        'scope_type' => ['point' => 1, 'knockdown' => 33, 'winner' => 12],
        'type_participant' => 'athlete',
        'status_type' => 'notstarted',
        'event_type' => 'prebet',
        'locked' => 'no',
        'weigh' => 100,
        'del' => 'no',
        'max_bet' => 10000.00,
        'max_payout' => 10000.00,
        'margin' => 108.00,
        'margin_prebet' => 108.00,
        'market' => [
            'OW' => 1,
            'OW_result_type' => 1,
            'max_bet' => 10000.00,
            'max_payout' => 10000.00,
            'stop_loss' => 1000000.00,
            'OW_weight' => 100,
            'match_result_type_id' => 1,
            'CS' => 1318,
            'CSR' => 1319,
            'T65' => 50,
            'OE' => 74,
            'KO1' => 1314,
            'KO2' => 1315,
            'KO3' => 1316,
            'KO4' => 1317,
        ],
    ],

    'inspired' => [
        'sport_id'      => 86,
        'country_id'    => 10,
        'gender'        => 'mixed',
        'user_id'       => 155,
        'amqp_exchange' => 'ivgbet',
        'amqp_key'      => 'ivg.event.',
        'allowed_ips'   => [ '10.1.4.47', '10.1.25.28', '185.16.228.226' ],
        'service_id'    => 2,
        'block_ips'     => true,
        //Horses = 0, Dogs = 1, Speedway = 2, F1Cars = 3, Football = 4, Numbers = 5, Trotting = 6, Cycling = 7 and Tennis = 8
        //category_id => ControllerId => some_id ?
        'sports' => [
            0 => [
                'category_id' => [ 101 => 1685, 102 => 1667, 103 => 1676,  104 => 3320, 105 => 1554, 106 => 3334 ],
                'sportform_prebet_id' => 168,
                'sportform_live_id' => 167,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            1 => [
                'category_id' => [ 101 => 1687, 102 => 1669, 103 => 1678, 104 => 3327, 105 => 1556, 106 => 3335 ],
                'sportform_prebet_id' => 164,
                'sportform_live_id' => 163,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            2 => [
                'category_id' => [ 101 => 1688, 102 => 1670, 103 => 1679, 104 => 3328, 105 => 1557, 106 => 3336 ],
                'sportform_prebet_id' => 174,
                'sportform_live_id' => 173,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            3 => [
                'category_id' => [ 101 => 1689, 102 => 1671, 103 => 1680, 104 => 3329, 105 => 1558, 106 => 3337 ],
                'sportform_prebet_id' => 166,
                'sportform_live_id' => 165,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            4 => [
                'category_id' => [ 101 => 1690, 102 => 1672, 103 => 1681, 104 => 2982, 105 => 1559, 106 => 3338 ],
                'sportform_prebet_id' => 172,
                'sportform_live_id' => 171,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            5 => [
                'category_id' => [ 101 => 1691, 102 => 1673, 103 => 1682, 104 => 3330, 105 => 1560, 106 => 3339 ],
                'sportform_prebet_id' => 170,
                'sportform_live_id' => 169,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            6 => [
                'category_id' => [ 101 => 1692, 102 => 1674, 103 => 1683, 104 => 3331, 105 => 1561, 106 => 3340 ],
                'sportform_prebet_id' => 178,
                'sportform_live_id' => 177,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            7 => [
                'category_id' => [ 101 => 1693, 102 => 1675, 103 => 1684, 104 => 3332, 105 => 1562, 106 => 3341 ],
                'sportform_prebet_id' => 162,
                'sportform_live_id' => 161,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ],
            8 => [
                'category_id' => [ 101 => 1686, 102 => 1668, 103 => 1677, 104 => 3333, 105 => 1555, 106 => 3278],
                'sportform_prebet_id' => 176,
                'sportform_live_id' => 175,
                'max_bet' => 3000,
                'max_payout' => 15000,
                'stop_loss' => 1000
            ]
        ]
    ],

    'betGames' => [
        'service_id' => 13,
        'token_expiration_time' => 1,
        'secret' => 'dfFWgbF3r4efr',
    ],
    'microgamingOrion' => [
        'baseUrl' => 'http://41.223.121.106/Orion/VanguardAdmin/SOAP2',
        'actionUrl' => 'http://mgsops.net/AdminAPI_Admin/IVanguardAdmin2/',
        'username' => 5034,
        'password' => 'test',
        'ns' => 'http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures',
        'serverId' => 5034
    ]
];
