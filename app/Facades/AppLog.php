<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AppLog
 * @package App\Facades
 *
 * @method static string alert($message, string $node = '', string $module = '', string $line = '')
 * @method static string critical($message, string $node = '', string $module = '', string $line = '')
 * @method static string error($message, string $node = '', string $module = '', string $line = '')
 * @method static string warning($message, string $node = '', string $module = '', string $line = '')
 * @method static string notice($message, string $node = '', string $module = '', string $line = '')
 * @method static string info($message, string $node = '', string $module = '', string $line = '')
 * @method static string debug($message, string $node = '', string $module = '', string $line = '')
 * @method static string log(string $level,string $message, string $node = '', string $module = '', string $line = '')
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
