<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;

/**
 * @property integer $participant_num
 * @property string $name
 * @property string $del
 * @property integer $weigh
 * @property string $name_format
 * @property integer $status_t
 * @property string  $last_update
 * @property integer $id
 *
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
        $connection->setFetchMode(\PDO::FETCH_ASSOC);

        return $connection
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

    public static function getOutcomeTypes(array $outcomeTypesIds)
    {
        /**@var Collection $templates*/
        $templates = static::whereIn('id', $outcomeTypesIds)->get();

        return  $templates->isNotEmpty() ? $templates : null;
    }
}
