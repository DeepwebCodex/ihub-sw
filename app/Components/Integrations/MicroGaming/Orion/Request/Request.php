<?php
namespace App\Components\Integrations\MicroGaming\Orion\Request;

use App\Components\ExternalServices\MicroGaming\Orion\SoapEmulator;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use function app;
use function GuzzleHttp\json_encode;

abstract class Request
{

    protected $uuid;
    protected $body;
    protected $soap;
    protected $client;
    protected $source;

    abstract function prepare(array $data = []);

    function __construct(SoapEmulator $client, SourceProcessor $source)
    {
        $this->client = $client;
        $this->source = $source;
    }

    public function getData(array $data = []): array
    {
        $this->prepare($data);
        $result = $this->client->sendRequest($this);
        $logRecord = [
            'data' => var_export($result, true)
        ];

        app('AppLog')->info(json_encode($logRecord), '', '', '', 'MicroGaming-Orion');

        return $this->parse($result);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function parse(string $result): array
    {
        $resultArray = $this->source->parser($result);
        return $resultArray;
    }
}
