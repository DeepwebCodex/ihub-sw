<?php

namespace App\Components\Integrations\GameSession\Exceptions;

use Exception;

/**
 * Class SessionDoesNotExist
 * @package App\Components\Integrations\GameSession\Exceptions
 */
class SessionDoesNotExist extends \RuntimeException
{
    public function __construct($message = 'Session does not exist', Exception $previous = null)
    {
        parent::__construct($message, 88618, $previous);
    }
}
