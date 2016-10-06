<?php

namespace App\Components\Integrations\LiveDealer;

use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;

class ApiService
{
    /**
     * Get transaction statistics from live dealer
     *
     * @return array
     * @throws \Exception
     */
    public function getStatistics()
    {
        $config = config('integrations.live_dealer');

        $date = date('Y-m-d');
        $systemId = $config['system_id'];
        $tid = microtime(true);
        $controller = 'Stats/Detailed';

        $url = $config['api_url'] . "System/Api/{$config['api_key']}/{$controller}";

        $hash = md5(
            "{$controller}/{$config['server_addr']}/{$tid}/{$config['api_key']}/{$date}/{$config['api_password']}"
        );
        $queryData = [
            'Date' => $date,
            'TID' => $tid,
            'Hash' => $hash,
            'Format' => 'json',
            'System' => $systemId
        ];

        $response = app('Guzzle')::request(
            'GET',
            $url,
            [
                RequestOptions::QUERY => $queryData
            ]
        );

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new \Exception('Not ok response code');
        }

        $data = $response->getBody();
        if (!$data) {
            throw new \Exception('Empty body response');
        }

        $decodedData = json_decode($data->getContents(), true);
        if (!isset($decodedData->data)) {
            throw new \Exception('Wrong response format. Missed "data" node in: ' . $decodedData);
        }

        return $decodedData->data;
    }
}
