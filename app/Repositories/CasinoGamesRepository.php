<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Class CasinoGamesRepository
 * @package App\Repositories
 */
class CasinoGamesRepository
{
    /**
     * @var string
     */
    protected $dbConnection = 'erlybet';

    /**
     * @param string $lang
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getAllGameTypes($lang)
    {
        return \DB::connection($this->dbConnection)
            ->table('cms.game_types AS gt')
            ->selectRaw('gt.*, gt_tr.trans AS game_type_tr')
            ->leftJoin('cms.game_types_tr AS gt_tr', function ($join) use ($lang) {
                /** @var JoinClause $join */
                $join->on('gt.id', 'gt_tr.game_type_id')
                    ->where('gt_tr.lang', $lang);
            })
            ->where('gt.public', true)
            ->get()
            ->all();
    }

    /**
     * @param $partnerId
     * @return array
     */
    public function getAllProviders($partnerId)
    {
        return \DB::connection($this->dbConnection)
            ->table('cms.provider AS p')
            ->selectRaw('*, p.name as provider, \'\' as provider_tr')
            ->join('cms.provider_partner AS pp', 'p.id', 'pp.provider_id')
            ->where([
                'pp.partner_id' => $partnerId,
                'p.enabled' => true,
                'pp.public' => true
            ])
            ->get()
            ->all();
    }

    /**
     * @param string $gameUrl
     * @param string $lang
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getGame($gameUrl, $lang)
    {
        return (array)\DB::connection($this->dbConnection)
            ->table('cms.games AS g')
            ->selectRaw(
                '*, p.name AS provider_name, g.id AS id, g.title AS game_name, 
                gd.description AS game_name_tr'
            )
            ->join('cms.provider as p', 'p.id', 'g.provider_id')
            ->leftJoin('cms.game_description as gd', function ($join) use ($lang) {
                /** @var JoinClause $join */
                $join->on('gd.game_id', 'g.id')
                    ->where('gd.lang', $lang);
            })
            ->leftJoin('cms.game_features as gf', function ($join) {
                /** @var JoinClause $join */
                $join->on('gf.game_id', 'g.id')
                    ->where('gf.lang', 'gd.lang');
            })
            ->where([
                'g.url' => $gameUrl,
                'g.enable' => true
            ])
            ->get()
            ->first();
    }

    /**
     * @param string $provider
     * @param string $gameType
     * @param string $lang
     * @param $partnerId
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getGames($provider, $gameType, $lang, $partnerId)
    {
        /** @var Builder $queryBuilder */
        $queryBuilder = DB::connection($this->dbConnection)
            ->table('cms.games AS g')
            ->select([
                'g.*',
                'gd.*',
                'gf.*',
                'p.*',
                'gt.*',
                'gtr.trans'
            ])
            ->leftJoin('cms.game_description AS gd', function ($join) use ($lang) {
                /** @var JoinClause $join */
                $join->on('g.id', 'gd.game_id')
                    ->where('gd.lang', $lang);
            })
            ->leftJoin('cms.game_features AS gf', function ($join) {
                /** @var JoinClause $join */
                $join->on('gf.game_id', 'g.id')->where('gf.lang', 'gd.lang');
            })
            ->join('cms.game_types_to_game AS gtg', 'gtg.game_id', 'g.id')
            ->join('cms.game_types AS gt', 'gt.id', 'gtg.game_type_id')
            ->leftJoin('cms.game_types_tr AS gtr', function ($join) {
                /** @var JoinClause $join */
                $join->on('gtr.game_type_id', 'gt.id')
                ->where('gd.lang', 'gtr.lang');
            })
            ->join('cms.games_provider_partner AS gpp', 'gpp.game_id', 'g.id')
            ->join('cms.provider_partner AS pp', function ($join) use ($partnerId) {
                /** @var JoinClause $join */
                $join->on('pp.id', 'gpp.provider_partner_id')
                    ->where('pp.partner_id', $partnerId);
            })
            ->join('cms.provider AS p', 'p.id', 'pp.provider_id')

            ->where([
                'g.enable' => 't',
                'gt.public' => 't',
                'p.enabled' => 't',
                'pp.public' => 't'
            ]);

        if ($provider !== 'allproviders') {
            $queryBuilder->where('p.name', strtolower($provider));
        }
        if ($gameType !== 'alltypes') {
            $queryBuilder->where('gt.game_type', strtolower($gameType));
        }

        return $queryBuilder->get()
            ->all();
    }

    /**
     * @param string $typeEntity
     * @param string $entityName
     * @param string $lang
     * @return array
     */
    public function getSeo($typeEntity, $entityName, $lang)
    {
        /** @var Builder $queryBuilder */
        $queryBuilder = \DB::connection($this->dbConnection)
            ->table('cms.game_seo AS s')
            ->selectRaw('s.*')
            ->where([
                's.type_entity' => $typeEntity,
                'lang' => $lang
            ]);

        if ($typeEntity === 'type') {
            $queryBuilder->join('cms.game_types gt', 'gt.id', 's.obj_id')
                ->where('gt.game_type', $entityName);
        } elseif ($typeEntity === 'provider') {
            $queryBuilder->join('cms.provider_partner pp', 'pp.id', 's.obj_id')
                ->join('cms.provider p', 'p.id', 'pp.provider_id')
                ->where('p.name', $entityName);
        } elseif ($typeEntity === 'game') {
            $queryBuilder->join('cms.games AS g', 'g.id', 's.obj_id')
                ->where('g.url', $entityName);
        }

        return $queryBuilder->get()
            ->all();
    }
}
