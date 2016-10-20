<?php

namespace App\Models\Erlybet\Integration;

use App\Models\Erlybet\BaseErlybetModel;

/**
 * Class GoldenRaceTransactionCashdesk
 * @package App\Models\Erlybet\Integration
 */
class GoldenRaceTransactionCashdesk extends BaseErlybetModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'integration.gr_transaction_cashdesk';

    /**
     * @param $barcode
     * @param $cashdeskId
     * @param $partnerId
     * @return static|null
     */
    public function getCard($barcode, $cashdeskId, $partnerId)
    {
        return static::where('status', 'completed')
            ->where('object_id', $barcode)
            ->where('cashdesk_id', $cashdeskId)
            ->where('partner_id', $partnerId)
            ->orderBy('id', 'asc')
            ->limit(1)
            ->first();
    }

    /**
     * @param array $states
     * @param $cashdeskId
     * @param $from
     * @param $to
     * @param $partnerId
     * @return static[]
     */
    public function getCards(array $states, $cashdeskId, $from, $to, $partnerId)
    {
        return static::where('status', 'completed')
            ->where('cashdesk_id', $cashdeskId)
            ->where('partner_id', $partnerId)
            ->whereIn('status_gr', $states)
            ->whereBetween('ut', [$from, $to])
            ->get();
    }
}
