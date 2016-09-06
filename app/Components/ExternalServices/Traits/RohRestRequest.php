<?php

namespace App\Components\ExternalServices\Traits;

/**
 * Request handling for account ROH get API
 */

use App\Exceptions\Api\ApiHttpException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait RohRestRequest
{
    protected function sendGetRoh(string $url, array $params, int $retry = 0){
        try {
            $response = app('Guzzle')::request(
                'GET',
                $url,
                [
                    RequestOptions::QUERY => $params
                ]
            );

            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < Response::HTTP_BAD_REQUEST) {
                if ($data = $response->getBody()) {
                    if ($data = json_decode($data->getContents(), true)) {
                        if(isset($data['status']) && $data['status'] == "error"){
                            throw new ApiHttpException(500, '');
                        }

                        if(isset($data['operations'])){
                            return $data['operations'];
                        }
                    }
                }

                throw new BadRequestHttpException();
            }

        } catch (\Exception $e) {
            throw new ApiHttpException(500, $e->getMessage());
        }
    }
}