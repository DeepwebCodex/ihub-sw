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
            $messageData = json_decode($message, true);
            if(is_array($messageData)){
                $dataMessage = array_merge($messageData, $dataMessage);
            } else {
                $dataMessage = array_merge($dataMessage, ['message' => $message]);
            }
        }

        parent::__construct($statusCode, $dataMessage ? json_encode($dataMessage) : '', $previous, $headers, $code);
    }

    public function getPayload(string $key){
        $data = json_decode($this->getMessage(), true);

        return array_get($data, $key, null);
    }
}