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
    const RESPONSE_BODY_MAX_CHARS = 255;

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
        return [
            'request' => [
                'url' => (string)$request->getUri(),
                'body' => (string)$request->getBody(),
            ],
            'response' => [
                'code' => $response ? $response->getStatusCode() : 'NULL',
                'body' => $this->getResponseBody($response, $error)
            ]
        ];
    }

    /**
     * @param ResponseInterface $response
     * @param \Exception $error
     * @return string
     */
    protected function getResponseBody(ResponseInterface $response = null, \Exception $error = null): string
    {
        if ($error !== null) {
            return $error->getMessage();
        }
        $responseBody = $response ? (string)$response->getBody() : '';
        return $this->truncateBody($responseBody);
    }

    /**
     * @param string $responseBody
     * @return string
     */
    protected function truncateBody(string $responseBody): string
    {
        if ($responseBody === '') {
            return $responseBody;
        }
        \preg_match('/^.{0,' . self::RESPONSE_BODY_MAX_CHARS . '}(?:.*?)\b/iu', $responseBody, $matches);
        $responseBody = $matches[0] ?? '';
        return $responseBody;
    }
}
