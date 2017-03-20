<?php

namespace App\Models;

use App\Components\Transactions\TransactionRequest;
use GuzzleHttp\Transaction;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $operation_id
 * @property integer $service_id
 * @property integer $amount
 * @property integer $move
 * @property integer $partner_id
 * @property integer $cashdesk
 * @property string $status
 * @property string $currency
 * @property string $foreign_id
 * @property string $transaction_type
 * @property integer $object_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $game_id
 * @property string $client_ip
*/
class Transactions extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'transaction_history';

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
        'transaction_type',
        'game_id',
        'client_ip'
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
     * @param int $partner_id
     * @return Transactions
     */
    public static function getTransaction(int $serviceId, $externalId, string $transactionType, int $partner_id){
        return Transactions::where([
            ['service_id', $serviceId],
            ['foreign_id', $externalId],
            ['transaction_type', $transactionType],
            ['partner_id', $partner_id]
        ])->first();
    }

    /**
     * @param int $serviceId
     * @param int $userId
     * @param $objectId
     * @param int $partner_id
     * @return Transactions
     */
    public static function getBetTransaction(int $serviceId, int $userId, $objectId, int $partner_id = null){
        $query = [
            ['service_id', $serviceId],
            ['user_id', $userId],
            ['object_id', $objectId],
            ['transaction_type', TransactionRequest::TRANS_BET],
            ['status', TransactionRequest::STATUS_COMPLETED]
        ];

        if ($partner_id !== null) {
            array_push($query, ['partner_id', $partner_id]);
        }

        return Transactions::where($query)->first();
    }

    /***
     * @param int $serviceId
     * @param int $userId
     * @param int $partnerId
     * @param int $gameId
     * @return Transactions
     */
    public static function getLastBetByUser(int $serviceId, int $userId, int $partnerId, int $gameId)
    {
        return Transactions::where([
            ['service_id', $serviceId],
            ['user_id', $userId],
            ['partner_id', $partnerId],
            ['game_id', $gameId],
            ['transaction_type', TransactionRequest::TRANS_BET],
            ['status', TransactionRequest::STATUS_COMPLETED]
        ])->orderBy('id', 'desc')->first();
    }

    /***
     * @param int $serviceId
     * @param int $userId
     * @param int $partnerId
     * @param int $gameId
     * @param string $foreignIid
     * @return Transactions
     */
    public static function getLastBetByUserWithForeightId(int $serviceId, int $userId, int $partnerId, int $gameId, string $foreignIid)
    {
        return Transactions::where([
            ['service_id', $serviceId],
            ['user_id', $userId],
            ['partner_id', $partnerId],
            ['game_id', $gameId],
            ['foreign_id', $foreignIid],
            ['transaction_type', TransactionRequest::TRANS_BET],
            ['status', TransactionRequest::STATUS_COMPLETED]
        ])->orderBy('id', 'desc')->first();
    }

    /***
     * @param int $serviceId
     * @param int $userId
     * @param string $currency
     * @param int $gameId
     * @param int $partnerId
     * @param int $transactionType
     * @param int $status
     * @return Transactions
     */
    public static function getLastNovomaticBet(int $serviceId, int $userId, string $currency, int $gameId, int $partnerId)
    {
        return Transactions::where([
            ['service_id', $serviceId],
            ['user_id', $userId],
            ['currency', $currency],
            ['game_id', $gameId],
            ['partner_id', $partnerId],
            ['transaction_type', TransactionRequest::TRANS_BET],
            ['status', TransactionRequest::STATUS_COMPLETED]
        ])->first();
    }
}
