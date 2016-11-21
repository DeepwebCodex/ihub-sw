<?php

namespace App\Components\Integrations\VirtualSports;

use App\Models\Line\Tournament as TournamentModel;

/**
 * Class Tournament
 * @package App\Components\Integrations\VirtualSports
 */
class Tournament
{
    use ConfigTrait;

    /**
     * @var TournamentModel
     */
    protected $tournamentModel;

    /**
     * Tournament constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param $tournamentName
     * @param $categoryId
     * @param $sportformId
     * @param $sportformLiveId
     * @return bool
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function create($tournamentName, $categoryId, $sportformId, $sportformLiveId):bool
    {
        $tournament = TournamentModel::findByNameForSport($tournamentName, $categoryId);
        if ($tournament === null) {
            Translate::add($tournamentName);

            $tournament = new TournamentModel([
                'name' => $tournamentName,
                'weigh' => $this->getConfigOption('weigh'),
                'enet_id' => null,
                'category_id' => $categoryId,
                'startdate' => null,
                'enddate' => null,
                'sportform_id' => $sportformId,
                'country_id' => $this->getConfigOption('country_id'),
                'enet_import' => $this->getConfigOption('enet_import'),
                'import_odds_provider' => $this->getConfigOption('import_odds_provider'),
                'max_bet' => $this->getConfigOption('max_bet'),
                'max_payout' => $this->getConfigOption('max_payout'),
                'stop_loss' => $this->getConfigOption('stop_loss'),
                'margin' => $this->getConfigOption('margin'),
                'margin_prebet' => $this->getConfigOption('margin_prebet'),
                'live_sportform_id' => $sportformLiveId,
                'gender' => $this->getConfigOption('gender'),
                'user_id' => $this->getConfigOption('user_id'),
                'sport_union_id' => $this->getConfigOption('sport_union_id'),
                'stop_loss_exp' => $this->getConfigOption('stop_loss_exp'),
                'max_bet_live' => $this->getConfigOption('max_bet_live'),
                'max_payout_live' => $this->getConfigOption('max_payout_live'),
                'info_url' => $this->getConfigOption('info_url'),
            ]);
            if (!$tournament->save()) {
                return false;
            }
        }
        $this->tournamentModel = $tournament;
        return true;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function getTournamentId():int
    {
        if (!$this->tournamentModel) {
            throw new \RuntimeException('Tournament not defined');
        }
        return (int)$this->tournamentModel->id;
    }
}
