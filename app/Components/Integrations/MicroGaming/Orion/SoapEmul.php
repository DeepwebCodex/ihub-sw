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
use Illuminate\Support\Facades\Config;



class SoapEmul extends Client{

    public function sendRequest(Request $request): string {
        try {


            $response = $this->request('POST', Config::get('integrations.microgamingOrion.baseUrl'), [
                'auth' => [Config::get('integrations.microgamingOrion.username'), Config::get('integrations.microgamingOrion.password')],
                'headers' => ['Content-Type' => 'text/xml',
                    'Request-Id' => $request->getUuid(),
                    'SOAPAction' => Config::get('integrations.microgamingOrion.actionUrl') . $request->getMethod()
                ],
                'body' => $request->getBody()
            ]);
            return $response->getBody();
        } catch (RequestException $e) {
            throw $e;
        }
    }

}
