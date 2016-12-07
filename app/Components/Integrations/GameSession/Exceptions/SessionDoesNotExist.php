<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 12/1/16
 * Time: 1:56 PM
 */

namespace App\Components\Integrations\GameSession\Exceptions;


use Exception;

class SessionDoesNotExist extends \RuntimeException
{
    public function __construct($message = "Session does not exist", Exception $previous = null)
    {
        parent::__construct($message, 88618, $previous);
    }
}