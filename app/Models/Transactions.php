<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    /**
     * {@inheritdoc}
     */
    //protected $connection = 'account'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'transactions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'operation_id',
        'service_id',
        'amount',
        'move',
        'partner_id',
        'cashdesk',
        'payment_instrument_id',
        'wallet_id',
        'wallet_account_id',
        'status',
        'currency',
        'foreign_id',
        'transaction_type'
    ];

    public function getOperationIdAttribute($value)
    {
        if(!$this->attributes['operation_id']) {
            return $this->id;
        } else {
            return $this->attributes['operation_id'];
        }
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
     * @param int $objectId
     * @return Transactions
     */
    public static function getBetTransaction(int $serviceId, int $userId, int $objectId){
        return Transactions::where([
            ['service_id', $serviceId],
            ['user_id', $userId],
            ['operation_id', $objectId]
        ])->first();
    }
}
