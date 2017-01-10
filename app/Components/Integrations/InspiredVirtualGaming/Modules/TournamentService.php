<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Modules;



use App\Models\Line\Tournament;
use App\Models\Trans\Trans;

class TournamentService
{
    private $eventName;
    private $categoryId;
    private $weight;
    private $sportFormId;
    private $countryId;
    private $maxBet;
    private $maxPayout;
    private $stopLoss;
    private $liveSportFormId;
    private $gender;
    private $user_id;

    public function __construct(
        string $eventName,
        int $categoryId,
        int $weight,
        int $sportFormId,
        int $countryId,
        float $maxBet,
        float $maxPayout,
        float $stopLoss,
        int $liveSportFormId,
        string $gender,
        int $user_id
    ) {
        $this->eventName = $eventName;
        $this->categoryId = $categoryId;
        $this->weight = $weight;
        $this->sportFormId = $sportFormId;
        $this->countryId = $countryId;
        $this->maxBet = $maxBet;
        $this->maxPayout = $maxPayout;
        $this->stopLoss = $stopLoss;
        $this->liveSportFormId = $liveSportFormId;
        $this->gender = $gender;
        $this->user_id = $user_id;
    }

    public function resolve() : Tournament
    {
        $eventName = (new Trans())->translate($this->eventName);

        $tournament = Tournament::findByNameForSport($eventName, $this->categoryId);

        if(!$tournament) {

            $tournament = Tournament::create([
                'name' => $eventName,
                'weigh' => $this->weight,
                'enet_id' => null,
                'category_id' => $this->categoryId,
                'sportform_id' => $this->sportFormId,
                'country_id' => $this->countryId,
                'enet_import' => 'no',
                'import_odds_provider' => 0,
                'max_bet' => $this->maxBet,
                'max_payout' => $this->maxPayout,
                'stop_loss' => $this->stopLoss,
                'margin' => 108,
                'margin_prebet' => 108,
                'live_sportform_id' => $this->liveSportFormId,
                'gender' => $this->gender,
                'user_id' => $this->user_id,
                'sport_union_id' => null,
                'stop_loss_exp' => 0
            ]);
        }

        if (!$tournament) {
            throw new \RuntimeException("Unable to get a tournament");
        }

        return $tournament;
    }
}