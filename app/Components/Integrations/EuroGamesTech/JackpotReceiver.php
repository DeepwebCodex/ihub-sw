<?php

namespace App\Components\Integrations\EuroGamesTech;

use App\Components\AppLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

/**
 * Class JackpotReceiver
 * @package App\Components\Integrations\EuroGamesTech
 */
class JackpotReceiver
{
    const NODE = 'egt';
    /**
     * @var AppLog
     */
    private $log;

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->log = new AppLog();
    }

    public function request()
    {
        try {
            $response = $this->client->request(
                'GET',
                config('integrations.egt.jackpot_url')
            );
        } catch (ConnectException $e) {
            $this->log->error($e->getMessage(), self::NODE);
            return null;
        }

        try {
            $content = $response->getBody()->getContents();
        } catch (\RuntimeException $e) {
            $content = null;
            $this->log->error($e->getMessage(), self::NODE);
        }

        return $content;
    }
}