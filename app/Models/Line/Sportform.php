<?php

namespace App\Models\Line;

/**
 * Class Sportform
 * @package App\Models\Line
 */
class Sportform extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'sportform';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param int $sportId
     */
    public static function findById(int $sportId)
    {
        return static::where('sport_id', $sportId)
            ->get()
            ->all();
    }

    /**
     * @param $sportId
     * @return array
     */
    public static function getSportFormIds(int $sportId):array
    {
        $sportForm = Sportform::findById($sportId);
        foreach ($sportForm as $item) {
            $itemId = $item['id'];
            if ($item['is_live']) {
                $liveSportFormId = $itemId;
            } else {
                $preBetSportFormId = $itemId;
            }
        }
        if (!isset($liveSportFormId, $preBetSportFormId)) {
            throw new \RuntimeException("Can't find sportform");
        }
        return [
            'live' => $liveSportFormId,
            'prebet' => $preBetSportFormId
        ];
    }
}
