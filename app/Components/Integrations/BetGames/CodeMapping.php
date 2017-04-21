<?php

namespace App\Components\Integrations\BetGames;


use iHubGrid\ErrorHandler\Http\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    const SUCCESS = 'success';
    const SIGNATURE = 'signature';
    const TIME = 'time';
    const TOKEN = 'token';

    public static function getMapping(){
        return [
            StatusCode::OK => [
                'message'   => '',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::SIGNATURE => [
                'message'   => 'wrong signature',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SIGNATURE]
            ],
            StatusCode::TIME => [
                'message'   => 'request is expired',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TIME]
            ],
            StatusCode::TOKEN=> [
                'message'   => 'invalid token',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TOKEN]
            ],

            StatusCode::INSUFFICIENT_FUNDS=> [
                'message' => 'insufficient funds',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TOKEN]
            ],

            StatusCode::BAD_OPERATION_ORDER => [
                'message' => 'there is no PAYIN with provided bet_id',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TOKEN]
            ],

            /** not implemented in Bet Games API */
            StatusCode::UNKNOWN => [
                'message' => 'unknown error',
                'map'       => [],
                'attribute' => null,
                'meanings'  => []
            ],

            StatusCode::DUPLICATED_WIN => [
                'message'   => '',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
        ];
    }

    public static function isAttribute($key):bool
    {
        return in_array($key, [
            self::SUCCESS, self::SIGNATURE, self::TIME, self::TOKEN,
            'params', 'params.method', 'params.amount', 'params.currency', 'params.bet_id',
            'params.transaction_id', 'params.retrying', 'params.player_id'
        ]);
    }
}