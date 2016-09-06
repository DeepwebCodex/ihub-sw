<?php

namespace App\Components\ExternalServices\Traits;

/**
 * Request handling for account ROH post API
 */

use App\Exceptions\Api\ApiHttpException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait RohRequest
{
    protected function sendPostRoh(string $url, array $params, int $retry = 0){
        try {
            $response = app('Guzzle')::request(
                'POST',
                $url,
                [
                    RequestOptions::HEADERS => [
                        'Accept' => 'application/json'
                    ],
                    RequestOptions::JSON => $params
                ]
            );

            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < Response::HTTP_BAD_REQUEST) {
                if ($data = $response->getBody()) {
                    if ($data = json_decode($data->getContents(), true)) {
                        //validate response data
                        if(isset($data['status']) && $data['status'] == 'error'){
                            throw new \Exception(json_encode($data['error']), isset($data['code']) ? $data['code'] : 0);
                        }

                        if(isset($data['response']) && !empty($data['response'])){
                            return $data['response'];
                        }
                    }
                }

                throw new BadRequestHttpException();
            }

        } catch (\Exception $e) {

            /*Retry operation on fail*/

            if($retry > 0){
                $retry --;
                $this->sendPostRoh($url, $params, $retry);
            }

            throw new ApiHttpException(500, $e->getMessage());
        }
    }
}