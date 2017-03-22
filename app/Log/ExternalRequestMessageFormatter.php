<?php

namespace App\Log;

use GuzzleHttp\MessageFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ExternalRequestMessageFormatter
 * @package App\Log
 */
class ExternalRequestMessageFormatter extends MessageFormatter
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param \Exception|null $error
     * @return string
     */
    public function format(
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $error = null
    ) {
        $responseBody = $response ? $response->getBody() : '';
        if ($responseBody) {
            $charsCount = 255;
            \preg_match('/^.{0,' . $charsCount. '}(?:.*?)\b/iu', $responseBody, $matches);
            $responseBody = $matches[0];
        }
        return [
            'request' => [
                'url' => (string)$request->getUri(),
                'body' => (string)$request->getBody(),
            ],
            'response' => [
                'code' => $response ? $response->getStatusCode() : 'NULL',
                'body' => $responseBody
            ]
        ];
    }
}
