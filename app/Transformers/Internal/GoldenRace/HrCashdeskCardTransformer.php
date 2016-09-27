<?php

namespace App\Transformers\Internal\GoldenRace;

use App\Models\Erlybet\Integration\GoldenRaceTransactionCashdesk;
use League\Fractal\TransformerAbstract;

/**
 * Class HrCashdeskCardTransformer
 * @package App\Transformers\Internal\GoldenRace
 */
class HrCashdeskCardTransformer extends TransformerAbstract
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
            'amount_with_tax' => (string)number_to_string($item->amount_with_taxes),
            'tax' => [
                [
                    'tax_percent' => (string)number_to_string($item->tax_percent),
                    'tax_amount' => (string)number_to_string($item->tax_amount),
                    'tax_type' => (int)$item->tax_type
                ]
            ]
        ];
    }
}
