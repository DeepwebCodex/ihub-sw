<?php

namespace App\Models\Line;

/**
 * Class ResultGame
 * @package App\Models\Line
 */
class ResultGame extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'result_game';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param array $records
     * @return mixed
     */
    public static function batchCreate(array $records)
    {
        return static::insert($records);
    }

    /**
     * @param int $eventId
     * @return mixed
     */
    public static function updateApprove(int $eventId)
    {
        return static::where([
            'event_id' => $eventId,
            'del' => 'no',
        ])->update('approve', 'yes');
    }

    /**
     * @param int $eventId
     * @param array $resultType
     * @param array $participant
     * @param array $scope
     * @param int $time
     */
    public function checkResultTable(int $eventId, array $resultType, array $participant, array $scope, $time = 0)
    {
        $recordsExist = \DB::connection($this->connection)
            ->table($this->table)
            ->where([
                'del' => 'no',
                'event_id' => $eventId
            ])->exists();

        if (!$recordsExist) {
            foreach ($resultType as $valueR) {
                foreach ($participant as $valueP) {
                    if ($valueP['name'] !== 'Blank') {
                        foreach ($scope as $valueS) {
                            static::create([
                                'event_id' => $eventId,
                                'scope_data_id' => $valueS['id'],
                                'result_type_id' => $valueR['id'],
                                'event_particpant_id' => $valueP['event_participant_id'],
                                'amount' => 0,
                                'result_time' => $time,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
