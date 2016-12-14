<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/2/16
 * Time: 10:25 AM
 */

namespace App\Components\ExternalServices;


use App\Components\ExternalServices\Traits\CashDeskRohRequest;
use App\Components\ExternalServices\Traits\RohRequest;
use App\Components\ExternalServices\Traits\RohRestRequest;
use App\Components\ExternalServices\Traits\SessionRequest;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * @param Request $request;
*/
class AccountManager
{
    use RohRequest;
    use SessionRequest;
    use RohRestRequest;
    use CashDeskRohRequest;

    const DEPOSIT = 0;
    const WITHDRAWAL = 1;

    const EUR = 'EUR';
    const GBP = 'GBP';
    const RUB = 'RUB';
    const UAH = 'UAH';
    const USD = 'USD';
    const BYR = 'BYR';
    const HRK = 'HRK';
    const AZN = 'AZN';

    //ROH erlang account manager
    private $accountRohHost;
    private $accountRohPort;

    //erland session manager
    private $sessionHost;
    private $sessionPort;

    //erlang card/stat manager
    private $restHost;
    private $restPort;

    //erlang card manager ROH
    private $cardsRohHost;
    private $cardsRohPort;

    //erlang cashdesk API point
    private $cashdeskRohHost;
    private $cashdeskRohPort;

    private $request;

    public function __construct()
    {

        $this->setUpConfig();


        $this->request = app('Request')::getFacadeRoot();
    }

    private function setUpConfig(){

        if(!config('external.api.account_roh') ||
            !config('external.api.account_session') ||
            !config('external.api.account_op') ||
            !config('external.api.cards_roh') ||
            !config('external.api.cash_desk_roh')) {

            throw new InvalidResourceException("Invalid API configuration");
        }

        $this->accountRohHost = config('external.api.account_roh.host');
        $this->accountRohPort = config('external.api.account_roh.port');

        $this->sessionHost = config('external.api.account_session.host');
        $this->sessionPort = config('external.api.account_session.port');

        $this->restHost = config('external.api.account_op.host');
        $this->restPort = config('external.api.account_op.port');

        $this->cardsRohHost = config('external.api.cards_roh.host');
        $this->cardsRohPort = config('external.api.cards_roh.port');

        $this->cashdeskRohHost = config('external.api.cash_desk_roh.host');
        $this->cashdeskRohPort = config('external.api.cash_desk_roh.port');
    }

    /**
     * Get user info by userID or ccid
     *
     * @param int $userId
     * @param bool $ccid
     * @return mixed
     */
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

    /**
     * @param string $login
     * @return mixed
     */
    public function checkLoginSession(string $login){
        return $this->getSession('session/exists', [
            'login' => $login
        ]);
    }

    /**
     * @return mixed
     */
    public function getFreeOperationId(){
        return $this->postMessageRoh('accounts/operation/get_free_operation_id', []);
    }

    /**
     * @return mixed
     */
    public function getFreeCardId(){
        return $this->postMessageCardRoh('cards/card/get_free_id',[]);
    }

    /**
     * Creates Account manager transaction operation for any account data manipulations
     *
     * @param string $status - pending|completed
     * @param int $service_id //integration service id - config
     * @param int $cashdesk //cachdesk id - config
     * @param int $user_id //integration user_id
     * @param $amount //sum amount to operate in wholes eg. 1, 0.5, 0.005
     * @param string $currency //currency EUR|GBP|RUB|UAH|USD
     * @param int $direction //transaction direction 0 - deposit, 1 - withdrawal
     * @param int $object_id //unique id of transaction integration side
     * @param string $comment //transaction comment
     * @return mixed
     */
    public function createTransaction(
        string $status,
        int $service_id, int $cashdesk, int $user_id,
        $amount, string $currency, int $direction, int $object_id, string $comment){

        return $this->postMessageRoh('accounts/operation/new', [
            'service_id'    => $service_id,
            'cashdesk'      => $cashdesk,
            'user_id'       => $user_id,
            'amount'        => $amount,
            'currency'      => $currency,
            'client_ip'     => get_client_ip() ?: '127.0.0.1',
            'move'          => $direction,
            'status'        => $status,
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
            'client_ip'     => get_client_ip() ?: '127.0.0.1',
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

    /**
     * Calculate card with bonus and taxes based on confagent $taxParams for specific partner
     *
     * @param int $partner_id
     * @param float $sum_in
     * @param string $currency
     * @param float $sum_out
     * @param array $taxParams
     * @return mixed
     */
    public function calcCardWithBonusAndTaxes(int $partner_id, float $sum_in, string $currency, float $sum_out, array $taxParams){
        return $this->postMessageCardRoh('cards/calculator/calc_card_with_bonus_and_tax',[
            "sum_in" => (float) $sum_in,
            "currency" => (string) $currency,
            "containers" => [
                [
                    "sum_out" => (float) $sum_out,
                    "result" => "win"
                ]
            ],
            "tax_type" => (int) $taxParams['tax_type'],
            "tax_rate" => (double) $taxParams['tax_rate'],
            "tax_type2" => (int) $taxParams['tax_type2'],
            "tax_rate2" => (double) $taxParams['tax_rate2'],
            "tax_type3" => (int) $taxParams['tax_type3'],
            "tax_rate3" => (double) $taxParams['tax_rate3'],
            "bonus_type" => (int) $taxParams['bonus_type'],
            "bonus_rate" => (double) $taxParams['bonus_rate'],
            "partner_id" => (int) $partner_id
        ]);
    }

    /**
     * Returns aggregated taxes values based on calcCardWithBonusAndTaxes response
     *
     * @param array $data
     * @return mixed
     */
    public function aggregateTaxes(array $data){
        //TODO::ger real input parameters for this action
        return $this->postMessageCardRoh('cards/select/aggr_taxes', $data);
    }

    /**
     * Gets some user info maybe this function is deprecated
     *
     * @param int $partner_id
     * @param int $ccid
     * @return mixed
     */
    public function getPlayerInfoByCcidForSccs(int $partner_id, int $ccid){
        return $this->postCashDesc('client_get_by_ccid.yaws', [
            'partner_id'    => $partner_id,
            'ccid'          => $ccid
        ]);
    }

    /**
     * Gets some user info maybe this function is deprecated
     *
     * @param int $partner_id
     * @param string $passport_se
     * @param int $passport_no
     * @return mixed
     */
    public function getPlayerInfoByPassportForSccs(int $partner_id, string $passport_se, int $passport_no){
        //we are expecting internal array from this request so reset is required to get first element
        $data = $this->postCashDesc('client_get_by_document', [
            'partner_id'    => $partner_id,
            'doc_seria'     => $passport_se,
            'doc_number'    => $passport_no
        ]);

        return is_array($data) ? reset($data) : $data;
    }


    /**
     * @param $cashDeskId
     * @return mixed
     */
    public function getCashDeskInfo($cashDeskId){
        return $this->postCashDesc('cashdesk_info.yaws', [
            'cashdesk_id' => $cashDeskId
        ]);
    }

    /*
     *
     * REST REQUEST PROXY FUNCTIONS --------------------------------------------
     * postMessageRoh
     * postMessageCardRoh
     * getSession
     * getRest
     * getCashDesc
     *
     */

    private function postMessageRoh(string $method, array $params, int $retry = 0)
    {
        return $this->sendPostRoh($this->buildRohHost($method), $params, $retry);
    }

    private function postMessageCardRoh(string $method, array $params, int $retry = 0)
    {
        return $this->sendPostRoh($this->buildCardsRohHost($method), $params, $retry);
    }

    private function getSession(string $method, array $params, int $retry = 0)
    {
        return $this->sendGetSession($this->buildSessionHost($method), $params, $retry);
    }

    private function getRest(string $method, array $params, int $retry = 0){
        return $this->sendGetRoh($this->buildRestHost($method), $params, $retry);
    }

    private function postCashDesc(string $method, array $params){
        return $this->sendPostCashDesk($this->buildCashdeskRohHost($method), $params);
    }

    /*
     *
     * HELPER FUNCTIONS TO BUILD REQUEST URLS --------------------------------------------
     * buildRohHost
     * buildSessionHost
     * buildRestHost
     * buildCardsRohHost
     * buildCashdeskRohHost
     *
     */

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

    private function buildCardsRohHost(string $method)
    {
        return 'http://' . $this->cardsRohHost . ':' . $this->cardsRohPort . '/' . $method;
    }

    private function buildCashdeskRohHost(string $method)
    {
        return 'http://' . $this->cashdeskRohHost . ':' . $this->cashdeskRohPort . '/' . $method;
    }
}