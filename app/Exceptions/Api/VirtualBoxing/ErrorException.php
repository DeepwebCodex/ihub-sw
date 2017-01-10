<?php

namespace App\Exceptions\Api\VirtualBoxing;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorException
 * @package App\Exceptions\Api\VirtualBoxing
 */
class ErrorException extends BaseException
{
    /**
     * ErrorException constructor.
     * @param string $message
     * @param array $payload
     */
    public function __construct($message, $payload = [])
    {
        parent::__construct($payload);
        $this->message = $message;
        $this->code = Response::HTTP_BAD_REQUEST;
    }
}
