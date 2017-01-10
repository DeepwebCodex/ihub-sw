<?php

namespace App\Components\Integrations\VirtualSports;

use App\Models\Trans\Trans;
use Illuminate\Support\Collection;

/**
 * Class TranslateService
 * @package App\Components\Integrations\VirtualSports
 */
class Translate
{
    /**
     * @var array
     */
    private static $transArr = [];

    /**
     * @return bool
     */
    public static function save()
    {
        foreach (self::$transArr as $value) {
            $item = [
                'key' => $value,
                'value' => $value
            ];
            (new Trans())->saveAs([
                'en' => $item,
                'ru' => $item,
            ]);
        }
        self::$transArr = [];
        return true;
    }

    /**
     * @param string $value
     */
    public static function add($value)
    {
        self::$transArr[] = $value;
    }


}
