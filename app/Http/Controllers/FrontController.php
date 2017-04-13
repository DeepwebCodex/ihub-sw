<?php

namespace App\Http\Controllers;

use App\Components\Integrations\Vermantia\Services\DataMapper;
use App\Components\Integrations\Vermantia\VermantiaDirectory;
use App\Console\Commands\Vermantia\Traits\DateParserTrait;
use App\Models\Line\Event;
use Carbon\Carbon;

class FrontController extends Controller
{
    use DateParserTrait;

    public function index()
    {
        $data = app('VermantiaGameService')->getResult(152591, 300);

        $dataEvent = $this->filterEventData($data, Carbon::now('UTC'));

        /*$market = collect(array_get($dataEvent, 'Market'))->where('ClassCode', '=', 'VF-FT')->first();

        $winningSection = collect($market)->get('WinningSelectionID');
        $op = collect(collect($market)->get('Selection'))->where('ID', '=', $winningSection)->first()['Description'];

        dd($op, $market, $winningSection);*/

        $dataMap = new DataMapper($dataEvent, array_get($dataEvent, 'EventType'));

        dd($dataMap->getMappedResults());

        $event = Event::findById(1602910);

        $participants = $event->preGetParticipant($event->id);

        dd($dataMap->getParticipants(), $participants);

        return view('welcome');
    }

    protected function filterEventData(array $rawData, Carbon $timeAfterRequest) : array
    {
        return collect($rawData)->filter(function($value, $key) {
            return in_array($key, VermantiaDirectory::eventNodesList());
        })->transform(function($item) use($rawData, $timeAfterRequest) {
            $item['dateDiff'] = $this->getTimeDiff($rawData['LocalTime'], $rawData['UtcTime'], $timeAfterRequest);
            return $item;
        })->first();
    }
}
