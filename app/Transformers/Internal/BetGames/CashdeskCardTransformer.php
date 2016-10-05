<?php

namespace App\Transformers\Internal\BetGames;

use App\Models\Erlybet\CardsBetGames;
use League\Fractal\TransformerAbstract;

/**
 * Class CashdeskCardTransformer
 * @package App\Transformers\Internal\BetGames
 */
class CashdeskCardTransformer extends TransformerAbstract
{
    /**
     * @param CardsBetGames $item
     * @return array
     */
    public function transform(CardsBetGames $item)
    {
        return [
            'status' => $item->status,
            'customer_id' => (int)$item->customer_id,
            'hash' => $item->hash,
            'sum' => \number_to_string($item->sum),
            'amount_won' => (string)$item->amount_won,
            'amount_with_tax' => (string)$item->amount_with_tax,
            'tax' => [
                [
                    'tax_percent' => (string)$item->tax_percent,
                    'tax_amount' => (string)$item->tax_amount,
                    'tax_type' => (int)$item->tax_type,
                ]
            ]
        ];
    }
}
