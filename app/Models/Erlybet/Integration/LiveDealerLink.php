<?php

namespace App\Models\Erlybet\Integration;

use App\Models\Erlybet\BaseErlybetModel;
use Illuminate\Database\Query\Builder;

/**
 * Class LiveDealerLink
 * @package App\Models\Erlybet\Integration
 */
class LiveDealerLink extends BaseErlybetModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'integration.ld_link';

    /**
     * Get data transactions for the period
     *
     * @param $gameIdFirst
     * @param $actionIdFirst
     * @param $gameIdLast
     * @param $actionIdLast
     * @return array
     */
    public function getTransactions($gameIdFirst, $actionIdFirst, $gameIdLast, $actionIdLast)
    {
        return \DB::connection($this->connection)
            ->table($this->table)
            ->select('user_id, game_id, i_actionid, amount, currency, type, balance_after')
            ->whereIn('id', function ($query) use ($gameIdFirst, $actionIdFirst, $gameIdLast, $actionIdLast) {
                /** @var Builder $query */
                $query->select('id')
                    ->where([
                        'game_id' => $gameIdFirst,
                        'i_actionid' => $actionIdFirst
                    ])->orWhere([
                        'game_id' => $gameIdLast,
                        'i_actionid' => $actionIdLast
                    ]);
            })
            ->orderBy('id')
            ->get()
            ->all();
    }
}
