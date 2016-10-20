<?php

namespace App\Exceptions\Api;

class ApiHttpException extends GenericApiHttpException
{
    /**
     * ApiHttpException constructor.
     * @param string $statusCode
     * @param null $message
     * @param array $payload
     * @param \Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct($statusCode, $message = null, array $payload = [], \Exception $previous = null, array $headers = array(), $code = 0)
    {
        parent::__construct($statusCode, $message, $payload, $previous, $headers, $code);
    }


}