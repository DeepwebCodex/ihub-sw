<?php

namespace App\Models\Line;

use Illuminate\Database\Query\JoinClause;

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
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param int $marketTemplateId
     * @return array
     */
    public function getOutcomeTypeByMarketTemplateId(int $marketTemplateId):array
    {
        $connection = \DB::connection($this->connection);

        return (array) $connection
            ->table('market_template AS mt')
            ->select('ot.*')
            ->join($this->table . ' AS ot', function ($join) {
                /** @var JoinClause $join */
                $join->whereRaw('ot.id = ANY (mt.outcome_types)');
            })
            ->where('mt.id', $marketTemplateId)
            ->get()
            ->all();
    }
}
