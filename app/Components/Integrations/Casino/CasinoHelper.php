<?php

namespace App\Components\Integrations\Casino;


class CasinoHelper
{
    public static function generateActionSignature(array $inputParams){
        //remove old signature and metadata from request array
        $data = array_except($inputParams, ['signature', '__response_meta']);

        ksort($data);
        return md5( implode("", $data) . config('integrations.casino.secret_word') );
    }
}