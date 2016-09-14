<?php

namespace App\Components\Integrations\EuroGamesTech;


class EgtHelper
{
    public static function generateDefenceCode(int $userId, string $currency, $time = null){

        if (!$time) {
            $time = time();
        }

        return md5($userId . $currency . config('integrations.egt.secret') . $time) . '-' . $time;
    }

    public static function getCurrencyFromPortalCode(string $portalCode){
        return substr($portalCode, -3);
    }
}