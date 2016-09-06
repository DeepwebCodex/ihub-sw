<?php

namespace App\Components\ExternalServices\Traits;

/**
 * Request handling for account ROH get session API
 */

use App\Exceptions\Api\ApiHttpException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait SessionRequest
{
    protected function sendGetSession(string $url, array $params, int $retry = 0){
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
                        if(isset($data['error'])){
                            throw new ApiHttpException(500, '', $data['error']);
                        }

                        if(isset($data['exists']) && !empty($data['exists'])){
                            return $data['exists'];
                        }
                    }
                }

                throw new BadRequestHttpException();
            }

        } catch (\Exception $e) {

            /*Retry operation on fail*/

            if($retry > 0){
                $retry --;
                $this->sendGetSession($url, $params, $retry);
            }

            throw new ApiHttpException(500, $e->getMessage());
        }
    }
}