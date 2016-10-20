<?php

namespace App\Transformers\Internal\GoldenRace;

use App\Models\Erlybet\Integration\GoldenRaceTransactionCashdesk;
use League\Fractal\TransformerAbstract;

/**
 * Class UaCashdeskCardTransformer
 * @package App\Transformers\Internal\GoldenRace
 */
class UaCashdeskCardTransformer extends TransformerAbstract
{
    /**
     * @param GoldenRaceTransactionCashdesk $item
     * @return array
     */
    public function transform(GoldenRaceTransactionCashdesk $item)
    {
        return [
            'status' => $item->status_gr,
            'number' => (string)$item->card_id,
            'gr_id' => (string)$item->object_id,
            'amount' => (string)number_to_string($item->amount),
            'dt' => $item->ut,
            'amount_with_tax' => (string)number_to_string(($item->status_gr === 'won') ? $item->amount : 0),
            'tax' => [
                [
                    'tax_percent' => (string)number_to_string(($item->status_gr === 'won') ? 1.5 : 0),
                    'tax_amount' => (string)number_to_string(
                        ($item->status_gr === 'won') ? (($item->amount * (1 / (1 - 0.195))) * 0.015) : 0
                    ),
                    'tax_type' => 2
                ],
                [
                    'tax_percent' => (string)number_to_string(($item->status_gr === 'won') ? 19.5 : 0),
                    'tax_amount' => (string)number_to_string(
                        ($item->status_gr === 'won') ? (($item->amount * (1 / (1 - 0.195))) * 0.18) : 0
                    ),
                    'tax_type' => 3
                ]
            ]
        ];
    }
}
