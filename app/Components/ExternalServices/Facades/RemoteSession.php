<?php

namespace App\Components\ExternalServices\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * RemoteSession Facade, supporting Laravel implementations.
 *
 * @method static \App\Components\ExternalServices\RemoteSession start($session_id)
 * @method static mixed get(string $key)
 * @method static bool set(array $data)
 * @method static bool exists(string $key)
 * @method static bool getSessionId()
 */
class RemoteSession extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'RemoteSession'; }
}