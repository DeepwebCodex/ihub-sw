<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AppLog
 * @package App\Facades
 */
class AppLog extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'AppLog';
    }
}
