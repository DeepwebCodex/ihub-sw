<?php

namespace App\Components\ExternalServices\Mysterion;

use App\Models\Transactions;
use League\Fractal\TransformerAbstract;

/**
 * Class TransactionTransformer
 * @package App\Transformers
 */
class TransactionTransformer extends TransformerAbstract
{
    /**
     * @param Transactions $transaction
     * @return array
     */
    public function transform(Transactions $transaction): array
    {
        return [
            'user_id' => (string)$transaction->getAttributeValue('user_id'),
            'event_type' => 'ihub_bet',
            'metrics' => [
                'partner_id' => (string)$transaction->getAttributeValue('partner_id'),
                'cashdesk' => (string)$transaction->getAttributeValue('cashdesk'),
                'move' => (int)$transaction->getAttributeValue('move'),
                'amount' => (float)$transaction->getAttributeValue('amount'),
                'currency' => (string)$transaction->getAttributeValue('currency'),
                'transaction_type' => (string)$transaction->getAttributeValue('transaction_type'),
                'service_id' => (int)$transaction->getAttributeValue('service_id'),
                'object_id' => (int)$transaction->getAttributeValue('object_id'),
                'game_id' => (string)$transaction->getAttributeValue('game_id')
            ]
        ];
    }
}
