<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RequestBase
 *
 * @author petroff
 */

namespace App\Components\Integrations\MicroGaming\Orion\Request;

use GuzzleHttp\ClientInterface;
use Nathanmac\Utilities\Parser\Facades\Parser;

abstract class Request {

    protected $method;
    protected $uuid;
    protected $body;
    protected $soap;
    protected $client;

    abstract function prepare();

    abstract function parse(string $result): array;

    function __construct(ClientInterface $client) {
        $this->client = $client;
    }

    protected function parseXml(string $result): array {
        $result = Parser::xml($result);
        return $result;
    }

    public function getData(): array {
        $this->prepare();
        $result = $this->client->sendRequest($this);
        return $this->parse($result);
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getUuid(): string {
        return $this->uuid;
    }

    public function getBody(): string {
        return $this->body;
    }

}
