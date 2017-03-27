<?php

namespace App\Components\ExternalServices\Mysterion;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MysterionService
 * @package App\Components\ExternalServices
 */
class SendService
{
    protected $config;

    /**
     * MysterionService constructor.
     */
    public function __construct()
    {
        $this->config = config('external.api.mysterion');
    }

    /**
     * @param string $data
     * @throws \RuntimeException
     */
    public function sendData(string $data)
    {
        $url = 'http://' . $this->config['host'] . ':' . $this->config['port'] . '/' . $this->config['action'];
        try {
            $response = app('Guzzle')->request(
                'POST',
                $url,
                [
                    RequestOptions::AUTH => [$this->config['sid'], $this->config['skey']],
                    RequestOptions::HEADERS => ['Accept' => 'application/json'],
                    RequestOptions::BODY => $data
                ]
            );
        } catch (RequestException $exception) {
            throw new \RuntimeException('Could not request Mysterion API: ' . $exception->getMessage());
        }
        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            throw new \RuntimeException('Not ok response code: ' . $response->getStatusCode());
        }
    }
}
