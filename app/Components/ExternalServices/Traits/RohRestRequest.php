<?php

namespace App\Components\ExternalServices\Traits;

/**
 * Request handling for account ROH get API
 */

use App\Exceptions\Api\GenericApiHttpException;
use App\Facades\AppLog;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait RohRestRequest
{
    protected function sendGetRoh(string $url, array $params, int $retry = 0){
        try {
            $response = app('Guzzle')->request(
                'GET',
                $url,
                [
                    RequestOptions::QUERY => $params
                ]
            );

            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < Response::HTTP_NOT_EXTENDED) {
                if ($data = $response->getBody()) {
                    if ($data = json_decode((string)$data, true)) {
                        if (isset($data['status']) && $data['status'] == "error") {
                            throw new GenericApiHttpException(500, '');
                        }
                        if (isset($data['operations'])) {
                            return $this->sanitize($data['operations']);
                        }
                    }
                }
                throw new BadRequestHttpException();
            }

        } catch (\Exception $e) {

            AppLog::critical([
                'message' => $e->getMessage(),
                'params'  => $params
            ]);

            throw new GenericApiHttpException(500, $e->getMessage());
        }
    }

    private function sanitize(array $data){
        $tempData = [];

        foreach ($data as $key => $value){
            if($value == "null"){
                $value = null;
            }

            $tempData[$key] = $value;
        }

        return $tempData;
    }
}