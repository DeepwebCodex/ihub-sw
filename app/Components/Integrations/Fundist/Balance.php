<?php


namespace App\Components\Integrations\Fundist;


class Balance
{
    public static function toFloat(int $balance)
    {
        $balance /= 100;
        return number_format($balance, 2, '.', '');
    }
}