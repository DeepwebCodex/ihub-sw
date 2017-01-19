<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\TextApiFormatter;
use App\Components\Integrations\InspiredVirtualGaming\EventProcessor;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\InspiredVirtualGamingTemplate;
use App\Http\Requests\InspiredVirtualGaming\BaseInspiredRequest;
use App\Http\Requests\InspiredVirtualGaming\EventCardRequest;
use App\Http\Requests\InspiredVirtualGaming\ResultRequest;
use App\Http\Requests\InspiredVirtualGaming\VoidRequest;
use App\Models\InspiredVirtualGaming\EventLink;
use Illuminate\Http\Response;
use Stringy\StaticStringy as S;

/**
 * Class CasinoController
 * @package App\Http\Controllers\Api
 */
class InspiredVirtualGaming extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = InspiredVirtualGamingTemplate::class;

    public function __construct(TextApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.inspired');

        $this->middleware('input.xml');
    }

    public function index(BaseInspiredRequest $request)
    {
        $method = $request->input('MessageType', 'error');

        $method = (string) S::camelize($method);

        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    public function eventCard(EventCardRequest $request)
    {
        $ivgControllerId = (int) $request->input('ControllerId');

        foreach ($request->input('events.event') as $eventData) {

            $ivgEventId = (int) array_get($eventData, 'EventId');

            if(!EventLink::isExists($ivgEventId))
            {
                $eventProcessor = new EventProcessor();

                $created = $eventProcessor->create(array_merge($eventData, [ 'ControllerId' => $ivgControllerId]));

                if(!$created) {
                    throw new \RuntimeException("Unable to create event");
                }
            } else {
                continue;
            }
        }

        return $this->respondOk();
    }

    public function result(ResultRequest $request)
    {
        $ivgEventId = $request->input('event.EventId');

        $processor = EventProcessor::getEvent((int) $ivgEventId);

        $processor->setResult($request->input());

        return $this->respondOk();
    }

    public function void(VoidRequest $request)
    {
        $processor = EventProcessor::getEvent((int) $request->input('event.EventId'));

        $processor->cancel();

        return $this->respondOk();
    }

    public function noMoreBets(VoidRequest $request)
    {
        $processor = EventProcessor::getEvent((int) $request->input('event.EventId'));

        $processor->stopBets();

        return $this->respondOk();
    }

    public function error()
    {
        throw new ApiHttpException(404, 'BADFORMAT');
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = 'ACK', array $payload = []){
        return parent::respondOk($statusCode, $message, $payload);
    }
}
