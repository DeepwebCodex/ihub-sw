<?php

namespace App\Components\Integrations\BetGames;


use App\Components\Integrations\CodeMappingBase;
use App\Components\Transactions\TransactionHelper;

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
        ];
    }
}