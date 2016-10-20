<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;

/**
 * Class FailedEventsRepository
 * @package App\Repositories
 */
class FailedEventsRepository
{
    /**
     * @var string
     */
    protected $dbConnection = 'line';

    /**
     * @param int $limit
     * @param string $all
     * @param int $categoryId
     * @param int $sportId
     * @return \Illuminate\Support\Collection
     */
    public function getEventIdList($limit, $all, $categoryId, $sportId)
    {
        /** @var Builder $queryBuilder */
        $queryBuilder = \DB::connection($this->dbConnection)
            ->table('event')
            ->join('tournament', 'event.tournament_id', '=', 'tournament.id')
            ->join('category', 'tournament.category_id', '=', 'category.id')
            ->join('sport', 'category.sport_id', '=', 'sport.id')
            ->where('sport.id', $sportId)
            ->where('event.del', 'no')
            ->whereIn('event.status_type', ['notstarted', 'inprogress']);

        if ($all === 'all') {
            if ($categoryId) {
                $queryBuilder->where('id', $categoryId);
            } else {
                $queryBuilder->whereRaw("dt < now() - interval '2 day'");
            }
        } else {
            $queryBuilder->whereRaw("dt between (now() - interval '5 day') AND (now() - interval '2 day')");
        }

        $queryBuilder->limit($limit);

        return $queryBuilder->pluck('event.id');
    }
}
