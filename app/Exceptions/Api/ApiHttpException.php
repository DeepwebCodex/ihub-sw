<?php

namespace App\Exceptions\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiHttpException extends HttpException
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
        $dataMessage = $payload;

        if($message) {
            if(json_decode($message)){
                $dataMessage = array_merge(json_decode($message, true), $dataMessage);
            } else {
                $dataMessage = array_merge(['message' => $message], $dataMessage);
            }
        }

        parent::__construct($statusCode, json_encode($dataMessage), $previous, $headers, $code);
    }
}