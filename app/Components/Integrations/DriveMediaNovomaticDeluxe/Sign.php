<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\DriveMediaNovomaticDeluxe;

use Illuminate\Support\Facades\Config;

/**
 * Description of Sign
 *
 * @author petroff
 */
class Sign {

    /**
     * @param array $data
     * @return string
     */
    static public function generate(array $data): string {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }

        $spaces = Config::get("integrations.DriveMediaNovomaticDeluxe.spaces");
        $currentSpace = $data['space'] ?? '';
        $secretKey = array_get($spaces, $currentSpace . ".key");
        $str = http_build_query($data);
        $hash = md5($secretKey . $str);

        return strtoupper($hash);
    }

}
