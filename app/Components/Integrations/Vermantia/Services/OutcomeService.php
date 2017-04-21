<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 11:52 AM
 */

namespace App\Components\Integrations\Vermantia\Services;



use App\Components\Integrations\Vermantia\MarketOutcomeMapping\MapRacer;
use App\Components\Integrations\Vermantia\MarketOutcomeMapping\VfCs;
use App\Components\Integrations\Vermantia\MarketOutcomeMapping\VfFt;
use App\Components\Integrations\Vermantia\MarketOutcomeMapping\VfMr;
use App\Components\Integrations\Vermantia\MarketOutcomeMapping\VfTg;
use App\Components\Integrations\Vermantia\MarketOutcomeMapping\VfUo;

class OutcomeService extends \App\Components\Integrations\VirtualSports\Services\OutcomeService
{
    protected $mappingRegistry = [
        'Win'    => MapRacer::class,
        'Place'  => MapRacer::class,
        'VF-MR'  => VfMr::class,
        'VF-CS'  => VfCs::class,
        'VF-TG'  => VfTg::class,
        'VF-FT'  => VfFt::class,
        'VF-UO'  => VfUo::class
    ];
}