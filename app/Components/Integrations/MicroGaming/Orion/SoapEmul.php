<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Soap
 *
 * @author petroff
 */

namespace App\Components\Integrations\MicroGaming\Orion;

use App\Components\Integrations\MicroGaming\Orion\Request\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function config;

class SoapEmul extends Client{

    public function sendRequest(Request $request): string {
        try {


            $response = $this->request('POST', config('integrations.microgamingOrion.baseUrl'), [
                'auth' => [config('integrations.microgamingOrion.username'), config('integrations.microgamingOrion.password')],
                'headers' => ['Content-Type' => 'text/xml',
                    'Request-Id' => $request->getUuid(),
                    'SOAPAction' => config('integrations.microgamingOrion.actionUrl') . $request->getMethod()
                ],
                'body' => $request->getBody()
            ]);
            return $response->getBody();
        } catch (RequestException $e) {
            throw $e;
        }
    }

}
