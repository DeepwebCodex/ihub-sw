<?php

namespace App\Components\ExternalServices;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AmqpService
 * @package App\Components\ExternalServices
 */
class AmqpService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('external.api.amqp');
    }

    /**
     * @param $exchange
     * @param $routingKey
     * @param $msgBody
     * @return bool
     * @throws \RuntimeException
     */
    public function sendMsg($exchange, $routingKey, $msgBody)
    {
        $url = 'http://' . $this->config['host'] . ':' . $this->config['port'] . '/api/mqsend';

        try {
            $response = app('Guzzle')->request(
                'POST',
                $url,
                [
                    RequestOptions::HEADERS => [
                        'Accept' => 'application/json'
                    ],
                    RequestOptions::FORM_PARAMS => [
                        'exchange' => $exchange,
                        'routing_key' => $routingKey,
                        'data' => $msgBody
                    ]
                ]
            );
        } catch (RequestException $exception) {
            throw new \RuntimeException('Could not request AMQP API');
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new \RuntimeException('Not ok response code');
        }

        $data = $response->getBody();
        if (!$data) {
            throw new \RuntimeException('Empty body response');
        }

        $decodedData = json_decode((string)$data, true);
        return (isset($decodedData['result']) && $decodedData['result'] === 'ok');
    }
}
