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
     * {@inheritdoc}
     */
    public $fillable = [
        'tournament_id', 'dt', 'name', 'locked', 'weigh', 'del', 'max_bet', 'max_payout', 'margin', 'margin_prebet',
        'stop_loss', 'user_id'
    ];

    /**
     * @param int $eventId
     * @return static|null
     */
    public static function findById(int $eventId)
    {
        return static::where('id', $eventId)
            ->first();
    }

    /**
     * @return \Illuminate\Database\Connection
     */
    protected function initConnection()
    {
        $connection = \DB::connection($this->connection);
        $connection->setFetchMode(\PDO::FETCH_ASSOC);
        return $connection;
    }

    /**
     * @param int $eventId
     * @param string $type
     * @return array
     */
    public function preGetScope(int $eventId, string $type):array
    {
        $sportform = $type === 'live' ? 'live_sportform_id' : 'sportform_id';

        return $this->initConnection()
            ->table($this->table)
            ->select('scope_data.id', 'scope_data.name', 'scope_data.description', 'scope_data.weigh', 'scope_data.auto')
            ->join('tournament', 'event.tournament_id', 'tournament.id')
            ->join('sportform', 'sportform.id', "tournament.{$sportform}")
            ->leftJoin('scope_data', function ($join) {
                /** @var JoinClause $join */
                $join->whereRaw('scope_data.id = ANY (sportform.scope_data)');
            })
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
     * @return array
     */
    public function preGetParticipant(int $eventId):array
    {
        return $this->initConnection()
            ->table($this->table)
            ->select('event_participant.*', 'participant.name', 'event_participant.id AS event_participant_id')
            ->join('event_participant', 'event_participant.event_id', 'event.id')
            ->join('participant', function ($join) {
                /** @var JoinClause $join */
                $join->on('event_participant.participant_id', 'participant.id')
                    ->whereNotNull('event_participant.number');
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
     * @return array
     */
    public function preGetPeriodStart(int $eventId, string $type):array
    {
        $sportform = $type === 'live' ? 'live_sportform_id' : 'sportform_id';

        return $this->initConnection()
            ->table($this->table)
            ->select('result_type.weigh', 'result_type.name', 'result_type.id')
            ->join('tournament', 'event.tournament_id', 'tournament.id')
            ->join('sportform', 'sportform.id', "tournament.{$sportform}")
            ->leftJoin('result_type', function ($join) {
                /** @var JoinClause $join */
                $join->whereRaw('result_type.id = ANY (sportform.result_types)');
            })
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
