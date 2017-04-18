<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
        $salt = Config::get("integrations.endorphina.salt");

        asort($data);
        $str = implode('', $data);
        $hash = sha1($str . $salt);
        return $hash;
    }

}
