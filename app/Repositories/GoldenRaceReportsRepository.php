<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;

/**
 * Class GoldenRaceReportsRepository
 * @package App\Repositories
 */
class GoldenRaceReportsRepository
{
    /**
     * @var string
     */
    protected $dbConnection = 'erlybet';

    /**
     * @var string
     */
    protected $table = 'integration.gr_transaction_cashdesk';

    /**
     * @param $partnerId
     * @param $from
     * @return array
     */
    public function getReport1($partnerId, $from)
    {
        $reportData = \DB::connection($this->dbConnection)
            ->table($this->table)
            ->select('card_id')
            ->where([
                'partner_id' => $partnerId,
                'status' => 'completed',
                'status_gr' => 'won',
                'status_sccs' => 'completed'
            ])
            ->whereBetween('ut', [$from, strtotime('+1 day', strtotime($from))])
            ->pluck('card_id')
            ->all();

        foreach ($reportData as &$value) {
            $value = (int)$value;
        }
        return $reportData;
    }

    /**
     * @param $partnerId
     * @param $from
     * @return array
     */
    public function getReport2($partnerId, $from)
    {
        $reportData = \DB::connection($this->dbConnection)
            ->table($this->table)
            ->select('card_id')
            ->where([
                'partner_id' => $partnerId,
                'move' => 1,
                'status' => 'completed',
                'status_gr' => 'won',
                'status_sccs' => 'completed'
            ])
            ->whereBetween('ut', [$from, strtotime('+1 day', strtotime($from))])
            ->whereNotIn('object_id', function ($query) use ($from) {
                /** @var Builder $query */
                $query->select('object_id')
                    ->where([
                        'move' => 0,
                        'status' => 'completed',
                        'status_gr' => 'won',
                        'status_sccs' => 'completed'
                    ])
                    ->whereBetween('ut', [$from, strtotime('+1 day', strtotime($from))]);
            })
            ->pluck('card_id')
            ->all();

        foreach ($reportData as &$value) {
            $value = (int)$value;
        }
        return $reportData;
    }

    /**
     * @param $partnerId
     * @param $from
     * @return array
     */
    public function getReport31($partnerId, $from)
    {
        $reportData = \DB::connection($this->dbConnection)
            ->table($this->table)
            ->selectRaw('SUM(amount) AS sum')
            ->selectRaw('COUNT(id) AS count')
            ->select('cashdesk_id')
            ->where([
                'partner_id' => $partnerId,
                'move' => 1,
                'status' => 'completed',
                'status_gr' => 'bet',
                'status_sccs' => 'completed'
            ])
            ->whereBetween('ut', [$from, strtotime('+1 day', strtotime($from))])
            ->get()
            ->all();

        foreach ($reportData as &$value) {
            $value = [
                'sum' => (double)$value['sum'],
                'count' => (int)$value['count'],
                'cashdesk_id' => (int)$value['cashdesk_id'],
            ];
        }
        return $reportData;
    }

    /**
     * @param $partnerId
     * @param $from
     * @return array
     */
    public function getReport32($partnerId, $from)
    {
        $rsumOut3 = 30000;
        $rsumOut4 = 500000;

        $reportData = \DB::connection($this->dbConnection)
            ->table($this->table)
            ->selectRaw(
                "SUM(amount_with_taxes), COUNT(id), cashdesk_id, 
                SUM(CASE WHEN (amount_with_taxes >= {$rsumOut3} 
                    AND amount_with_taxes < {$rsumOut4}) 
                    THEN amount_with_taxes ELSE 0 END ) sum1,
                SUM(CASE WHEN (amount_with_taxes >= {$rsumOut3} AND amount_with_taxes < {$rsumOut4}) 
                THEN 1 ELSE 0 END) AS cnt1,
                SUM(CASE WHEN amount_with_taxes >= {$rsumOut3} 
                THEN amount_with_taxes ELSE 0 END) sum2,
                SUM(CASE WHEN amount_with_taxes >= {$rsumOut3} 
                THEN 1 ELSE 0 END) AS cnt2"
            )
            ->where([
                'partner_id' => $partnerId,
                'move' => 0,
                'status' => 'completed',
                'status_gr' => 'won',
                'status_sccs' => 'completed'
            ])
            ->whereBetween('ut', [$from, strtotime('+1 day', strtotime($from))])
            ->groupBy('cashdesk_id')
            ->get()
            ->all();

        foreach ($reportData as &$value) {
            $value = [
                'sum' => (double)$value['sum'],
                'count' => (int)$value['count'],
                'cashdesk_id' => (int)$value['cashdesk_id'],
                'sum1' => (double)$value['sum1'],
                'cnt1' => (int)$value['cnt1'],
                'sum2' => (double)$value['sum2'],
                'cnt2' => (int)$value['cnt2'],
            ];
        }
        return $reportData;
    }

    /**
     * @param $partnerId
     * @param $from
     * @return array
     */
    public function getReport33($partnerId, $from)
    {
        $reportData = \DB::connection($this->dbConnection)
            ->table($this->table)
            ->selectRaw('SUM(amount) AS sum')
            ->selectRaw('COUNT(id) AS count')
            ->select('cashdesk_id')
            ->where([
                'partner_id' => $partnerId,
                'move' => 0,
                'status' => 'completed',
                'status_gr' => 'cancel',
                'status_sccs' => 'completed'
            ])
            ->whereBetween('ut', [$from, strtotime('+1 day', strtotime($from))])
            ->groupBy('cashdesk_id')
            ->get()
            ->all();

        foreach ($reportData as &$value) {
            $value = [
                'sum' => (double)$value['sum'],
                'count' => (int)$value['count'],
                'cashdesk_id' => (int)$value['cashdesk_id'],
            ];
        }
        return $reportData;
    }
}
