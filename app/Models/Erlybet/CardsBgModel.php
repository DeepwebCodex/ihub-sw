<?php

namespace App\Models\Erlybet;

/**
 * Class CardsBgModel
 * @package App\Models\Erlybet
 */
class CardsBgModel extends BaseErlybetModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'cards_bg';

    /**
     * @return float
     */
    public function getSumAttribute()
    {
        return (float)$this->amount / 100;
    }

    /**
     * @return mixed|string
     */
    public function getStatusAttribute()
    {
        $status = $this->state;
        if ($this->state === 'SOLD') {
            if ($this->is_revise) {
                $status = $this->is_won ? 'WIN' : 'LOSE';
            } else {
                $status = 'UNDEFINED';
            }
        }
        return $status;
    }

    /**
     * @param $value
     * @return float
     */
    public function getAmountWonAttribute($value)
    {
        if (strtotime($this->ut) <= strtotime('2016-06-30 21:00:00')) {
            return round($value / 10000, 2);
        }
        return $value;
    }

    /**
     * @param $barcode
     * @param $cashdeskId
     * @return mixed
     */
    public function getCard($barcode, $cashdeskId)
    {
        return static::where('barcode', $barcode)
            ->where('cashdesk_id', $cashdeskId)
            ->whereRaw('hash IS NOT NULL')
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();
    }

    /**
     * @param array $states
     * @param $cashdeskId
     * @param $from
     * @param $to
     * @return mixed
     */
    public function getCards(array $states, $cashdeskId, $from, $to)
    {
        return static::where('cashdesk_id', $cashdeskId)
            ->whereIn('state', $states)
            ->whereBetween('ut', [$from, $to])
            ->whereRaw('hash IS NOT NULL')
            ->orderBy('id', 'desc')
            ->get();
    }
}
