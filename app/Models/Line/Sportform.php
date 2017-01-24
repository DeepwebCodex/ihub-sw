<?php

namespace App\Models\Line;

use Illuminate\Database\Query\JoinClause;

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

    public static function getNumParticipants(int $tournamentId)
    {
        $tournamentTable = (new Tournament())->getTable();

        return static::join($tournamentTable, function($join) use($tournamentTable, $tournamentId){
                /** @var JoinClause $join */
                $join->on((new static())->getTable().'.id', $tournamentTable.'.sportform_id')->where($tournamentTable.'.id', $tournamentId);
            })
            ->value('participant_num');
    }

    /**
     * @param $sportId
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
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
