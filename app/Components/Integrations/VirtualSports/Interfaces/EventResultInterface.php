<?php

namespace App\Components\Integrations\VirtualSports\Interfaces;

use App\Components\Integrations\InspiredVirtualGaming\Modules\CategoryService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\DataMapper;
use App\Components\Integrations\InspiredVirtualGaming\Modules\EventService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\OutcomeService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\TournamentService;
use App\Components\Integrations\VirtualSports\Result;
use App\Components\Traits\ConfigTrait;
use App\Models\Line\Category;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\MarketTemplate;
use App\Models\Line\Outcome;
use App\Models\Line\OutcomeType;
use App\Models\Line\ResultGame;
use App\Models\Line\ResultGameTotal;
use App\Models\Line\StatusDesc;
use App\Models\Line\Tournament;
use Illuminate\Database\Eloquent\Collection;

interface EventResultInterface
{
    public function process() : int;

    public function finishEvent();
}