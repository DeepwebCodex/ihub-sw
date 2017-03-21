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
     * {@inheritdoc}
     */
    public $fillable = [
        'event_id', 'scope_data_id', 'result_type_id', 'event_particpant_id', 'amount', 'staff_id', 'result_time'
    ];

    /**
     * @param int $eventId
     * @return mixed
     */
    public static function updateApprove(int $eventId)
    {
        return static::where([
            'event_id' => $eventId,
            'del' => 'no',
        ])->update(['approve' => 'yes']);
    }

    public static function isResultsApproved(int $eventId) : bool
    {
        return ! ((bool) static::where([
            'event_id' => $eventId,
            'approve'  => 'no'
        ])->count());
    }

    /**
     * @param int $eventId
     * @param array $resultTypes
     * @param array $participants
     * @param array $scopes
     * @param int $time
     */
    public function checkResultTable(int $eventId, array $resultTypes, array $participants, array $scopes, $time = 0)
    {
        $recordsExist = \DB::connection($this->connection)
            ->table($this->table)
            ->where([
                'del' => 'no',
                'event_id' => $eventId
            ])->exists();

        if ($recordsExist) {
            return;
        }

        $data = [];
        foreach ($resultTypes as $resultType) {
            foreach ($participants as $participant) {
                if ($participant['name'] !== 'Blank') {
                    foreach ($scopes as $scope) {
                        $data[] = [
                            'event_id' => $eventId,
                            'scope_data_id' => $scope['id'],
                            'result_type_id' => $resultType['id'],
                            'event_particpant_id' => $participant['event_participant_id'],
                            'amount' => 0,
                            'result_time' => $time,
                        ];
                    }
                }
            }
        }
        static::insert($data);
    }

    public static function getResult(int $event_id) {
        return static::where('event_id', $event_id)->where('amount', 0, '>')->first();
    }
}
