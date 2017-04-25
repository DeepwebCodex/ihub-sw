<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/2/16
 * Time: 10:25 AM
 */

namespace App\Components\ExternalServices\Vermantia;

use App\Components\ExternalServices\Traits\VermantiaRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * @param Request $request;
*/
class VermantiaGameService
{
    use VermantiaRequest;

    private $host;
    private $port;

    public function __construct()
    {
        $this->setUpConfig();

        $this->request = app('Request')::getFacadeRoot();
    }

    private function setUpConfig(){
        if(!config('integrations.vermantia.game_server.host') ||
            !config('integrations.vermantia.game_server.port') ) {

            throw new InvalidResourceException("Invalid API configuration");
        }

        $this->host = config('integrations.vermantia.game_server.host');
        $this->port = config('integrations.vermantia.game_server.port');
    }

    public function getServerTime()
    {
        return $this->getRest('ServerTime', []);
    }

    public function getUpcomingEvents(int $hours, string $type = null)
    {
        return $this->getRest('UpcomingEvents', [
            'hours' => $hours,
            'type'  => $type
        ]);
    }

    public function getEvents(Carbon $date)
    {
        return $this->getRest("Events/{$date->format('Y')}/{$date->format('m')}/{$date->format('d')}", []);
    }

    public function getPreviousAndNextEvents(int $count = 1, string $type = null)
    {
        return $this->getRest('PreviousAndNextEvents', [
            'count' => $count,
            'type'  => $type
        ]);
    }

    public function getResults(Carbon $date)
    {
        return $this->getRest("Results/{$date->format('Y')}/{$date->format('m')}/{$date->format('d')}", []);
    }

    public function getEvent(int $eventId)
    {
        return $this->getRest("Event/{$eventId}", []);
    }

    public function getResult(int $eventId, int $waitSeconds = null)
    {
        return $this->getRest("Result/{$eventId}", [
            'wait' => $waitSeconds
        ]);
    }

    public function getEventForm(int $eventId)
    {
        return $this->getRest("EventForm/{$eventId}", []);
    }

    public function getRaceEventCombinationOdds(int $eventId)
    {
        return $this->getRest("RaceEventCombinationOdds/{$eventId}", []);
    }

    public function getFootballLeagueTeamStandings()
    {
        return $this->getRest("FootballLeagueTeamStandings", []);
    }

    public function openEvent(int $eventId, int $mappedEventId = null, int $mappedScheduleID = null)
    {
        return $this->getRest("OpenEvent/{$eventId}", [
            'eventID' => $mappedEventId,
            'scheduleID' => $mappedScheduleID
        ]);
    }

    public function closeEvent(int $eventId)
    {
        return $this->getRest("CloseEvent/{$eventId}", []);
    }

    private function getRest(string $method, array $params, int $retry = 3)
    {
        return $this->sendGet($this->buildGameServerHost($method), $params, $retry);
    }

    private function buildGameServerHost(string $method)
    {
        return $this->host . ':' . $this->port . '/VseGameServer/DataService/' . $method;
    }
}