<?php

namespace App\Models\Line;

/**
 * Class OutcomeType
 * @package App\Models\Line
 */
class OutcomeType extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'outcome_type';

    /**
     * @param $marketId
     * @return array
     */
    public function getOutcomeType($marketId)
    {
        return \DB::connection($this->connection)
            ->table('market_template mt')
            ->join('outcome_type ot', 'ot.id = any (mt.outcome_types)')
            ->where('mt.id', $marketId)
            ->get()
            ->all();
    }
}
