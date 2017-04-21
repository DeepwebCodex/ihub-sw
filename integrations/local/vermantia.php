<?php

return [
    'service_id' => 57,
    'sport_id' => 179, //TODO::Get sport id for vermantia
    'country_id' => 10,
    'gender' => 'mixed',
    'user_id' => 155,
    'amqp' => [
        'exchange' => 'calculator',
        'key' => 'calc'
    ],
    'game_server' => [
        'host' => 'http://62.38.57.179',
        'port' => 8013
    ],
    'market' => [
        'result_type_id' => 514
    ],
    'sports' => [
        'PlatinumHounds' => [
            'name' => 'Greyhounds',
            'game_result_scope_id' => 11,
            'sportform_prebet_id' => 780,
            'sportform_live_id' => 779,
            'max_bet' => 10000,
            'max_payout' => 10000,
            'stop_loss' => 1000000,
            'markets' => [
                'Win'   => [1119],
                'Place' => [1122]
            ]
        ],
        'Football' => [
            'name' => 'Football',
            'game_result_scope_id' => 1,
            'sportform_prebet_id' => 781,
            'sportform_live_id' => 782,
            'max_bet' => 10000,
            'max_payout' => 10000,
            'stop_loss' => 1000000,
            'markets' => [
                'VF-MR'   => [1], //Match Result
                'VF-CS'   => [1110], //Correct score
                //'VF-DR'   => [], //Team that will lead in first/second half time !
                'VF-TG'   => [1111], //Total goals
                //'VF-FG'   => [], //First goal scored in quoter
                'VF-FT'   => [200], //First team to score
                //'VF-FS'   => [], //First player to score a goal !
                //'VF-PM'   => [], //Match had a penalty
                //'VF-YM'   => [], //There was a yellow card in match !
                'VF-UO'   => [50], //Under over
                //'VF-DI'   => [], //Double chance IN !
                //'VF-DO'   => [], //Double chance OUT !
                //'VF-DX'   => []  //Double chance IN/OUT !
            ]
        ]
    ]
];