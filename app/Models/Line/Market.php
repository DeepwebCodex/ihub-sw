<?php

namespace App\Models\Line;

/**
 * Class Market
 * @package App\Models\Line
 */
class Market extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'market';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public $fillable = [
        'event_id', 'market_template_id', 'result_type_id', 'max_bet', 'max_payout', 'stop_loss', 'service_id',
        'user_id'
    ];

    /**
     * @param int $eventId
     * @param string $suspend
     * @return bool
     */
    protected function updateMarketEventSuspend(int $eventId, string $suspend):bool
    {
        return (bool)\DB::connection($this->connection)
            ->table($this->table)
            ->where('event_id', $eventId)
            ->update(['suspend' => $suspend]);
    }

    /**
     * @param int $eventId
     * @return bool
     */
    public function suspendMarketEvent(int $eventId):bool
    {
        return $this->updateMarketEventSuspend($eventId, 'yes');
    }

    /**
     * @param int $eventId
     * @return bool
     */
    public function resumeMarketEvent(int $eventId):bool
    {
        return $this->updateMarketEventSuspend($eventId, 'no');
    }
}
