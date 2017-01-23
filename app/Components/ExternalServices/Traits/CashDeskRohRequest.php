<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/5/16
 * Time: 3:39 PM
 */

namespace App\Components\ExternalServices\Traits;


use App\Exceptions\Api\GenericApiHttpException;
use App\Facades\AppLog;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait CashDeskRohRequest
{
    protected function sendPostCashDesk(string $url, array $params, int $retry = 0){
        try {
            $response = app('Guzzle')::request(
                'POST',
                $url,
                [
                    RequestOptions::HEADERS => [
                        'Accept' => 'application/json'
                    ],
                    RequestOptions::FORM_PARAMS => $params
                ]
            );

            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < Response::HTTP_NOT_EXTENDED) {
                if ($data = $response->getBody()) {

                    return $this->processError($data->getContents());

                }

                throw new BadRequestHttpException();
            }

        } catch (\Exception $e) {

            /*Retry operation on fail*/

            if($retry > 0){
                $retry --;
                $this->sendPostCashDesk($url, $params, $retry);
            }

            AppLog::critical([
                'message' => $e->getMessage(),
                'params'  => $params
            ]);

            throw new GenericApiHttpException(500, $e->getMessage());
        }
    }


    /**
     * @param $data - response body contents
     * @return mixed
     * @throws \Exception
     */
    private function processError($data){
        if($response = json_decode($data, true)){
            if(isset($response['cashdesk_id'])){
                return $response;
            }

            if(!isset($response['ok']) && !isset($response['error'])){
                throw new \Exception($response);
            } else if (isset($response['error'])){
                throw new \Exception($response['error']);
            }

            if(isset($response['ok']) && !empty($response['ok'])){
                return $response['ok'];
            }

        } else {
            throw new \Exception($data);
        }
    }
}