<?php


namespace App\Components\Integrations\NetEnt;


class Balance
{
    public static function toFloat(int $balance)
    {
        $balance /= 100;
        return number_format($balance, 2, '.', '');
    }
}