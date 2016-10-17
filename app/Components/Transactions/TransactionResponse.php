<?php

namespace App\Components\Transactions;


use App\Components\ExternalServices\AccountManager;
use App\Exceptions\Api\ApiHttpException;

/**
 * @property integer $operation_id
 * @property integer $service_id
 * @property integer $cashdesk
 * @property integer $user_id
 * @property integer $payment_instrument_id
 * @property string $wallet_id
 * @property string $wallet_account_id
 * @property integer $partner_id
 * @property integer $move
 * @property string $status
 * @property string $dt
 * @property string $dt_done
 * @property integer $object_id
 * @property float $amount
 * @property string $currency
 * @property string $client_ip
 * @property string $comment
 * @property float $deposit_rest
 * @property string $window
 * @property string $staff_id
 * @property string $hash
 * @property string $data
 * @property integer $tax_type
 * @property integer $tax_rate
 * @property integer $tax_sum
 * @property integer $fee
 */
class TransactionResponse
{
    private $attributes = [];

    public $isDuplicate = false;
    public $balance;

    public function __construct(array $data, bool $isDuplicate, $balance = 0)
    {
        $this->attributes  = $data;
        $this->isDuplicate = $isDuplicate;
        $this->balance = $this->procBalance($balance);
    }

    public function setDuplicate(bool $isDuplicate){
        $this->isDuplicate = $isDuplicate;
    }

    public function setBalance(float $balance){
        $this->balance = $balance;
    }

    public function getBalance(){
        return $this->balance;
    }

    public function getBalanceInCents()
    {
        $balance = $this->getBalance();

        if($balance !== null){
            return $balance * 100;
        }

        return null;
    }

    public function isDuplicate(){
        return $this->isDuplicate;
    }

    public function __get($name)
    {
        return array_get($this->attributes, $name);
    }

    protected function procBalance($balance = 0){
        if(!$this->deposit_rest && $balance){
            return $balance;
        }

        return $this->deposit_rest;
    }

    public function getAttributes(){
        return $this->attributes;
    }
}