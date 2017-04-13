<?php

namespace App\Console\Commands\Vermantia;

use App\Components\Integrations\Vermantia\EventProcessor;
use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;

class CancelEvent extends BaseEventCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vermantia:cancel-event {event-id : Id of an event for signal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels Vermantia events';

    protected $eventId;

    public function __construct()
    {
        parent::__construct('Vermantia', 'vermantia');
    }

    public function runHandle()
    {
        $this->eventId = (int) $this->argument('event-id');

        if($this->eventId) {

            $processor = EventProcessor::getEvent((int) $this->eventId);

            $processor->cancel();

            $this->respondOk("Event: {$this->eventId} ({$processor->getEventId()}) was canceled");

        }

        $this->respondOk(CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::MISS_ELEMENT));
    }
}
