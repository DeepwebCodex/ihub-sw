<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 11:52 AM
 */

namespace App\Components\Integrations\VirtualBox\Services;

use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapCS;
use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapKO1;
use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapKO2;
use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapKO3;
use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapKO4;
use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapOE;
use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapOW;
use App\Components\Integrations\VirtualBox\MarketOutcomeMapping\MapT65;

class OutcomeService extends \App\Components\Integrations\VirtualSports\Services\OutcomeService
{
    protected $mappingRegistry = [
        'CS'  => MapCS::class,
        'OW'  => MapOW::class,
        'CSR' => MapCS::class,
        'T65' => MapT65::class,
        'OE'  => MapOE::class,
        'KO1' => MapKO1::class,
        'KO2' => MapKO2::class,
        'KO3' => MapKO3::class,
        'KO4' => MapKO4::class
    ];
}