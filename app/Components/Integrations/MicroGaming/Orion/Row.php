<?php
namespace App\Components\Integrations\MicroGaming\Orion;

class Row
{

    static function is32bitSignedInt($value)
    {
        return (abs($value) & 0x7FFFFFFF) === abs($value);
    }
}
