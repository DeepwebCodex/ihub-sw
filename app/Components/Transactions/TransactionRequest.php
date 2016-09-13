<?php

namespace App\Components\Transactions;


use App\Components\ExternalServices\AccountManager;
use App\Exceptions\Api\ApiHttpException;

/**
 * @property  AccountManager $accountManager
 */
class TransactionRequest
{
    const D_DEPOSIT = 0;
    const D_WITHDRAWAL = 1;

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    public $service_id;
    public $cashdesk_id;
    public $user_id;
    public $amount;
    public $currency;
    public $direction;
    public $object_id;
    public $comment;

    public function __construct(int $service_id, int $object_id, int $user_id, string $currency, int $direction, float $amount)
    {
        $this->service_id  = $service_id;
        $this->object_id   = $object_id;
        $this->user_id     = $user_id;
        $this->direction   = $direction;
        $this->amount      = $amount;
        $this->currency    = $currency;

        $this->comment     = json_encode($this->getComment());

        $this->cashdesk_id = app('Request')::getFacadeRoot()->server('FRONTEND_NUM', 0);
    }

    public function getComment()
    {
        return [
            "comment"   => ($this->direction ? 'Withdrawal' : 'Deposit') . ' for object_id: ' . $this->object_id,
            "amount"    => $this->amount,
            "currency"  => $this->currency
        ];
    }
}