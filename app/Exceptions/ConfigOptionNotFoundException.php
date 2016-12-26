<?php

namespace App\Exceptions;

use Exception;

/**
 * Class ConfigOptionNotFoundException
 * @package App\Exceptions
 */
class ConfigOptionNotFoundException extends \Exception
{
    public function __construct($message = 'Configuration error', $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
