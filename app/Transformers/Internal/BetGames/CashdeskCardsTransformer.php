<?php

namespace App\Transformers\Internal\BetGames;

use App\Models\Erlybet\CardsBetGamesModel;
use League\Fractal\TransformerAbstract;

/**
 * Class CashdeskCardsTransformer
 * @package App\Transformers\Internal\BetGames
 */
class CashdeskCardsTransformer extends TransformerAbstract
{
    /**
     * @param CardsBetGamesModel $item
     * @return array
     */
    public function transform(CardsBetGamesModel $item)
    {
        return [
            'status' => $item->status,
            'number' => $item->barcode,
            'sum' => \number_to_string($item->sum),
            'dt' => $item->ut,
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
