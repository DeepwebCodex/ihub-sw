<?php

namespace App\Components\ExternalServices\MicroGaming\Orion;

use App\Components\Integrations\MicroGaming\Orion\Request\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;


class SoapEmulator
{

    public function sendRequest(Request $request): string
    {
        try {
            $response = app('Guzzle')->request(
                'POST',
                Config::get('integrations.microgamingOrion.baseUrl'),
                array_merge(
                    [
                        'auth' => [
                            Config::get('integrations.microgamingOrion.username'),
                            Config::get('integrations.microgamingOrion.password')
                        ],
                        'headers' => [
                            'Content-Type' => 'text/xml',
                            'Request-Id' => $request->getUuid(),
                            'SOAPAction' => Config::get('integrations.microgamingOrion.actionUrl') . $request::REQUEST_NAME
                        ],
                        'body' => $request->getBody(),
                    ],
                    config('integrations.common.proxyExternalRequest', [])
                )
            );
            return $response->getBody();
        } catch (RequestException $e) {
            throw $e;
        }
    }

}
