<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/3/16
 * Time: 5:55 PM
 */

namespace App\Models\InspiredVirtualGaming;

use Illuminate\Database\Query\JoinClause;

/**
 * Class EventLink
 * @package App\Models\VirtualBoxing
 */
class OutcomeLink extends BaseInspiredModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'outcome_link';

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public static function getTemplates(int $marketTemplateId)
    {
        return static::where('market_template_id', $marketTemplateId)
            ->join('outcome_type', function($join){
                /** @var JoinClause $join */
                $join->on('outcome_link.outcome_template_id', 'outcome_type.id');
            })->get()->all();
    }

    public function getIParams(array $eventData) : array
    {
        if($handicap = $this->getIParamHandicap($eventData)) {
            return $handicap;
        }

        return [
            $this->getIParam($eventData, 'iparam1'),
            $this->getIParam($eventData, 'iparam2')
        ];
    }

    private function getIParamHandicap(array $eventData)
    {
        if($this->market_template_id == 30 && isset($eventData['wdls'])){
            if ((float) array_get($eventData, 'wdl.0.Price') > (float) array_get($eventData, 'wdl.2.Price')) {
                return [1, 0];
            } else {
                return [0, 1];
            }
        }

        return null;
    }

    private function getIParam(array $eventData, string $attribute)
    {
        if(!is_numeric(data_get($this, $attribute)))
        {
            return data_get($eventData, data_get($this, $attribute));
        }

        return (int) data_get($this, $attribute);
    }
}
