<?php

namespace App\Components\Transactions;


use App\Components\ExternalServices\AccountManager;
use ReflectionClass;

/**
 * @property  AccountManager $accountManager
 */
class TransactionRequest
{
    const D_DEPOSIT = 0;
    const D_WITHDRAWAL = 1;

    const STATUS_NULL = null;
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    const TRANS_BET = 'bet';
    const TRANS_WIN = 'win';
    const TRANS_REFUND = 'refund';
    const TRANS_BONUS_WIN = 'bonus_win';

    public $service_id;
    public $partner_id;
    public $cashdesk_id;
    public $user_id;
    public $amount;
    public $currency;
    public $direction;
    public $object_id;
    public $comment;
    public $game_id;

    public $transaction_type;
    public $foreign_id;

    /**
     * TransactionRequest constructor.
     * @param int $service_id
     * @param int $object_id
     * @param int $user_id
     * @param string $currency
     * @param int $direction
     * @param float $amount
     * @param $transaction_type
     * @param $foreign_id
     * @param $game_id
     * @param int $partner_id
     * @param int $cashdesk_id
     */
    public function __construct(
        int $service_id,
        int $object_id,
        int $user_id,
        string $currency,
        int $direction,
        float $amount,
        $transaction_type,
        $foreign_id,
        $game_id,
        int $partner_id = null,
        int $cashdesk_id = null
    ) {
        $this->service_id = $service_id;
        $this->object_id = $object_id;
        $this->user_id = $user_id;
        $this->direction = $direction;
        $this->amount = $amount;
        $this->currency = $currency;

        $this->game_id = $game_id;

        $this->transaction_type = $transaction_type;

        $this->foreign_id = $foreign_id;

        $this->comment = json_encode($this->getComment());


        $this->partner_id = $partner_id ?? app('Request')::getFacadeRoot()->server('PARTNER_ID');
        $this->cashdesk_id = $cashdesk_id ?? app('Request')::getFacadeRoot()->server('FRONTEND_NUM');

    }

    public function getComment()
    {
        return [
            "comment"   => ($this->direction ? 'Withdrawal' : 'Deposit') . ' for object_id: ' . $this->object_id,
            "amount"    => $this->amount,
            "currency"  => $this->currency
        ];
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $reflect = new ReflectionClass($this);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
        $props_ret = [];

        foreach ($props as $property){
            /**@var \ReflectionProperty $property*/
            $props_ret[$property->getName()] = $property->getValue($this);
        }

        return $props_ret;
    }
}