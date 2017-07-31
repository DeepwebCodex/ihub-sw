<?php

namespace App\Components\ExternalServices\FinanceCashflow\Traits;

use GuzzleHttp\RequestOptions;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait FinanceServiceRequest
{
    private $responseCode = 0;

    protected function sendPost(string $url, array $params, int $retry = 0)
    {
        try {
            $response = app('Guzzle')->request(
                'POST',
                $url,
                [
                    RequestOptions::HEADERS => ['Accept' => 'application/json'],
                    RequestOptions::JSON    => $params,
                ]
            );

            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < Response::HTTP_NOT_EXTENDED) {
                if ($data = $response->getBody()) {
                    if ($data = json_decode((string)$data, true)) {
                        //validate response data
                        if (isset($data['status']) && $data['status'] == 'error') {
                            throw new \Exception(json_encode($data['error']),
                                isset($data['error']['code']) ? $data['error']['code'] : 0);
                        }

                        return true;
                    }
                }

                throw new BadRequestHttpException();
            }

        } catch (\Exception $e) {
            $statusCode = $this->getHttpCode($e->getCode());

            /*Retry operation on fail*/

            if ($retry > 0 && ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR || $statusCode == Response::HTTP_SERVICE_UNAVAILABLE)) {
                $retry--;
                sleep(1);
                return $this->sendPostRoh($url, $params, $retry);
            }

            \app('AppLog')->warning(
                [
                    'message' => $e->getMessage(),
                    'params' => $params
                ],
                '',
                '',
                '',
                'finance-api'
            );

            throw new GenericApiHttpException($statusCode, $e->getMessage(), [], null, [], $e->getCode());
        }
    }
}