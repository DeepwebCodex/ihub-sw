<?php

namespace App\Models;

use App\Components\Transactions\TransactionRequest;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'erlybet'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'integration.transaction_history';

    protected $fillable = [
        'user_id',
        'operation_id',
        'service_id',
        'amount',
        'move',
        'partner_id',
        'cashdesk',
        'status',
        'currency',
        'foreign_id',
        'object_id',
        'transaction_type'
    ];

    public function getAmountAttribute($value){
        return abs($value) / 100;
    }

    public function setAmountAttribute($value){
        $this->attributes['amount'] = abs($value) * 100;
    }

    /**
     * @param int $serviceId
     * @param $externalId
     * @param string $transactionType
     * @return Transactions
     */
    public static function getTransaction(int $serviceId, $externalId, string $transactionType){
        return Transactions::where([
            ['service_id', $serviceId],
            ['foreign_id', $externalId],
            ['transaction_type', $transactionType]
        ])->first();
    }

    /**
     * @param int $serviceId
     * @param int $userId
     * @param $objectId
     * @return Transactions
     */
    public static function getBetTransaction(int $serviceId, int $userId, $objectId){
        return Transactions::where([
            ['service_id', $serviceId],
            ['user_id', $userId],
            ['object_id', $objectId],
            ['transaction_type', TransactionRequest::TRANS_BET],
            ['status', TransactionRequest::STATUS_COMPLETED]
        ])->first();
    }
}
