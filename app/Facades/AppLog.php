<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AppLog
 * @package App\Facades
 *
 * @method static string alert(string $message, string $node = '', string $module = '', string $line = '')
 * @method static string critical(string $message, string $node = '', string $module = '', string $line = '')
 * @method static string error(string $message, string $node = '', string $module = '', string $line = '')
 * @method static string warning(string $message, string $node = '', string $module = '', string $line = '')
 * @method static string notice(string $message, string $node = '', string $module = '', string $line = '')
 * @method static string info(string $message, string $node = '', string $module = '', string $line = '')
 * @method static string debug(string $message, string $node = '', string $module = '', string $line = '')
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
