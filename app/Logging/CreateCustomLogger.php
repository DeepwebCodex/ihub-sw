<?php

namespace App\Logging;

use iHubGrid\ErrorHandler\Log\Logger;

class CreateCustomLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array $config
     * @return Logger
     */
    public function __invoke(array $config)
    {
        return new Logger($config);
    }
}