<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/2/16
 * Time: 10:25 AM
 */

namespace App\Components\ExternalServices;


use App\Exceptions\Api\ApiHttpException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * @param Request $request;
*/
class AccountManager
{
    const DEPOSIT = 0;
    const WITHDRAWAL = 1;

    const EUR = 'EUR';
    const GBP = 'GBP';
    const RUB = 'RUB';
    const UAH = 'UAH';
    const USD = 'USD';

    //ROH erlang account manager
    private $accountRohHost;
    private $accountRohPort;

    //erland session manager
    private $sessionHost;
    private $sessionPort;

    //erlang card/stat manager
    private $restHost;
    private $restPort;

    private $request;

    public function __construct()
    {

        $this->setUpConfig();


        $this->request = app('Request')::getFacadeRoot();
    }

    private function setUpConfig(){

        if(!config('external.api.account_roh') || !config('external.api.account_session') || !config('external.api.account_op')) {
            throw new InvalidResourceException("Invalid API configuration");
        }

        $this->accountRohHost = config('external.api.account_roh.host');
        $this->accountRohPort = config('external.api.account_roh.port');

        $this->sessionHost = config('external.api.account_session.host');
        $this->sessionPort = config('external.api.account_session.port');

        $this->restHost = config('external.api.account_op.host');
        $this->restPort = config('external.api.account_op.port');
    }

    public function getUserInfo(int $userId, bool $ccid = false)
    {
        $params = [
            'id' => (int)$userId
        ];

        if($ccid){
            $params = [
                'ccid' => (int)$userId
            ];
        }

        return $this->postMessageRoh('accounts/account/get', $params);
    }

    public function checkLoginSession(string $login){
        return $this->getSession('session/exists', [
            'login' => $login
        ]);
    }

    public function getFreeOperationId(){
        return $this->postMessageRoh('accounts/operation/get_free_operation_id', []);
    }

    /**
     * Creates Account manager transaction operation for any account data manipulations
     *
     * @param int $service_id   //integration service id - config
     * @param int $cashdesk     //cachdesk id - config
     * @param $user_id          //integration user_id
     * @param $amount           //sum amount to operate in wholes eg. 1, 0.5, 0.005
     * @param string $currency  //currency EUR|GBP|RUB|UAH|USD
     * @param int $direction    //transaction direction 0 - deposit, 1 - withdrawal
     * @param int $object_id    //unique id of transaction integration side
     * @param string $comment   //transaction comment
     * @return mixed
     */
    public function createTransaction(
        int $service_id, int $cashdesk, int $user_id,
        $amount, string $currency, int $direction, int $object_id, string $comment){

        return $this->postMessageRoh('accounts/operation/new', [
            'service_id'    => $service_id,
            'cashdesk'      => $cashdesk,
            'user_id'       => $user_id,
            'amount'        => $amount,
            'currency'      => $currency,
            'client_ip'     => $this->request->ip() ?: '127.0.0.1',
            'move'          => $direction,
            'status'        => 'pending',
            'object_id'     => $object_id,
            'comment'       => $comment,
            'partner_id'    => (int) $this->request->server('PARTNER_ID')
        ], 3);
    }

    /**
     * Cancels Account manager transaction operation for created transactions in pending status
     *
     * @param int $operation_id - roh internal operation id
     * @param string $comment - cancel comment
     * @return mixed
     */
    public function cancelTransaction(int $operation_id, string $comment){
        return $this->postMessageRoh('accounts/operation/cancel', [
            'operation_id'  => $operation_id,
            'comment'       => $comment
        ], 3);
    }

    /**
     * Sets account manager transaction operation as completed - so commiting changes to user balance
     *
     * @param int $user_id          //integration user_id
     * @param int $operation_id     //roh internal operation id
     * @param int $direction        //transaction direction 0 - deposit, 1 - withdrawal
     * @param int $object_id        //unique id of transaction integration side
     * @param string $currency      //currency EUR|GBP|RUB|UAH|USD
     * @param string $comment       //transaction comment
     * @return mixed
     */
    public function commitTransaction(
        int $user_id, int $operation_id,
        int $direction, int $object_id,
        string $currency, string $comment){

        return $this->postMessageRoh('accounts/operation/completed', [
            'user_id'       => $user_id,
            'operation_id'  => $operation_id,
            'client_ip'     => $this->request->ip() ?: '127.0.0.1',
            'move'          => $direction,
            'object_id'     => $object_id,
            'currency'      => $currency,
            'comment'       => $comment
        ], 3);
    }

    /**
     * Retrieves operations history from account manager system
     *
     * @param int|null $user_id
     * @param int|null $direction
     * @param int|null $object_id
     * @param int|null $service_id
     * @return mixed
     */
    public function getOperations(int $user_id = null, int $direction = null, int $object_id = null, int $service_id = null){
        return $this->getRest('operations/get', [
            'user_id'       => $user_id,
            'move'          => $direction,
            'object_id'     => $object_id,
            'service_id'    => $service_id
        ], 3);
    }

    private function postMessageRoh(string $method, array $params, int $retry = 0)
    {
        try {
            $response = app('Guzzle')::request(
                'POST',
                $this->buildRohHost($method),
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
            }

        } catch (\Exception $e) {

            /*Retry operation on fail*/

            if($retry > 0){
                $retry --;
                $this->postMessageRoh($method, $params, $retry);
            }

            throw new ApiHttpException(500, $e->getMessage());
        }
    }

    private function getSession(string $method, array $params, int $retry=0){
        try {
            $response = app('Guzzle')::request(
                'GET',
                $this->buildSessionHost($method),
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
            }

        } catch (\Exception $e) {

            /*Retry operation on fail*/

            if($retry > 0){
                $retry --;
                $this->getSession($method, $params, $retry);
            }

            throw new ApiHttpException(500, $e->getMessage());
        }
    }

    private function getRest(string $method, array $params){
        try {
            $response = app('Guzzle')::request(
                'GET',
                $this->buildRestHost($method),
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
            }

        } catch (\Exception $e) {
            throw new ApiHttpException(500, $e->getMessage());
        }
    }

    private function buildRohHost(string $method)
    {
        return 'http://' . $this->accountRohHost . ':' . $this->accountRohPort . '/' . $method;
    }

    private function buildSessionHost(string $method)
    {
        return 'http://' . $this->sessionHost . ':' . $this->sessionPort . '/' . $method;
    }

    private function buildRestHost(string $method)
    {
        return 'http://' . $this->restHost . ':' . $this->restPort . '/' . $method;
    }
}