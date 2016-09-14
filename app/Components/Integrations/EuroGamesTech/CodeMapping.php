<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/13/16
 * Time: 11:10 AM
 */

namespace App\Components\Integrations\EuroGamesTech;


use App\Components\Integrations\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    public static function getMapping(){
        return [
            1000 => [
                'message'   => 'OK',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            1100 => [
                'message'   => 'Transaction duplicate',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::DUPLICATE]
            ],
            1300 => [
                'message'   => 'Success (player protection)',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            1400 => [
                'message'   => 'Success - time is almost out (player protection)',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            1500 => [
                'message'   => 'Success - low funds (player protection)',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            2000 => [
                'message'   => 'Timed out',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TIMED_OUT]
            ],
            3000 => [
                'message'   => 'Server error',
                'map'       => [],
                'attribute' => null,
                'default'   => true,
                'meanings'  => [self::SERVER_ERROR, self::USER_NOT_FOUND]
            ],
            3100 => [
                'message'   => 'Insufficient funds',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::NO_MONEY]
            ],
            3300 => [
                'message'   => 'Bet limit reached',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::BET_LIMIT]
            ],
            3400 => [
                'message'   => 'Loss limit reached',
                'map'       => [],
                'attribute' => null,
                'meanings'  => []
            ],
            3500 => [
                'message'   => 'Session time limit reached',
                'map'       => [],
                'attribute' => null,
                'meanings'  => []
            ]
        ];
    }
}