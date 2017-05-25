<?php

namespace App\Components\Integrations\Endorphina;

use Illuminate\Support\Facades\Config;

/**
 * Description of Sign
 *
 * @author petroff
 */
class Sign
{

    static public function generate(array $data): string
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        $partnerId = app('GameSession')->get('partner_id') ?? null;
        $salt = Config::get("integrations.endorphina.partners_config.{$partnerId}.salt");

        ksort($data);
        $str = implode('', $data);
        $hash = sha1($str . $salt);
        return strtoupper($hash);
    }

}
