<?php

namespace App\Transformers\Internal\Bg;

use App\Models\Erlybet\CardsBgModel;
use League\Fractal\TransformerAbstract;

/**
 * Class CashdeskCardTransformer
 * @package App\Transformers\Internal\Bg
 */
class CashdeskCardTransformer extends TransformerAbstract
{
    public function transform(CardsBgModel $item)
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
