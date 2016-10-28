<?php

namespace App\Models\Line;

use Illuminate\Database\Query\JoinClause;

/**
 * Class Event
 * @package App\Models\Line
 */
class Event extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'event';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param int $eventId
     * @return bool
     */
    public static function findById(int $eventId)
    {
        return static::where('id', $eventId)
            ->get()
            ->first();
    }

    /**
     * @param int $eventId
     * @param string $type
     * @return mixed
     */
    public function preGetScope(int $eventId, string $type)
    {
        $sportform = $type === 'live' ? 'live_sportform_id' : 'sportform_id';

        return \DB::connection($this->connection)
            ->table($this->table)
            ->select('scope_data.id', 'scope_data.name', 'scope_data.description', 'scope_data.weigh', 'scope_data.auto')
            ->join('tournament', 'event.tournament_id', 'tournament.id')
            ->join('sportform', 'sportform.id', "tournament.{$sportform}")
            ->leftJoin('scope_data', 'scope_data.id = any (sportform.scope_data)')
            ->where([
                'event.id' => $eventId,
                'event.del' => 'no'
            ])
            ->orderBy('scope_data.weigh')
            ->get()
            ->all();
    }

    /**
     * @param int $eventId
     * @return mixed
     */
    public function preGetParticipant(int $eventId)
    {
        return \DB::connection($this->connection)
            ->table($this->table)
            ->select('event_participant.*', 'participant.name', 'event_participant.id AS event_participant_id')
            ->join('event_participant', 'event_participant.event_id', 'event.id')
            ->join('participant', function ($join) {
                /** @var JoinClause $join */
                $join->on('event_participant.participant_id', 'participant.id')
                    ->where('event_participant.number is not null');
            })
            ->where([
                'event.id' => $eventId,
                'event.del' => 'no'
            ])
            ->orderBy('event_participant.number')
            ->get()
            ->all();
    }

    /**
     * @param int $eventId
     * @param string $type
     * @return mixed
     */
    public function preGetPeriodStart(int $eventId, string $type)
    {
        $sportform = $type === 'live' ? 'live_sportform_id' : 'sportform_id';

        return \DB::connection($this->connection)
            ->table($this->table)
            ->select('result_type.weigh', 'result_type.name', 'result_type.id')
            ->join('tournament', 'event.tournament_id', 'tournament.id')
            ->join('sportform', 'sportform.id', "tournament.{$sportform}")
            ->leftJoin('result_type', 'result_type.id = any (sportform.result_types)')
            ->where([
                'event.id' => $eventId,
                'event.del' => 'no'
            ])
            ->orderBy('result_type.weigh')
            ->limit(2)
            ->get()
            ->all();
    }
}
