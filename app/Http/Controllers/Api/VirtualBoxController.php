<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\TextApiFormatter;
use App\Components\Integrations\VirtualBox\EventProcessor;
use App\Components\Integrations\VirtualBox\Services\DataMapper;
use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\VirtualBoxingTemplate;
use App\Http\Requests\VirtualBoxing\MatchBetRequest;
use App\Http\Requests\VirtualBoxing\MatchProgressRequest;
use App\Http\Requests\VirtualBoxing\ResultRequest;
use App\Models\VirtualBoxing\EventLink;
use App\Models\VirtualBoxing\Result;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stringy\StaticStringy as S;

/**
 * Class VirtualBoxController
 * @package App\Http\Controllers\Api
 */
class VirtualBoxController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = VirtualBoxingTemplate::class;

    /**
     * VirtualBoxingController constructor.
     * @param TextApiFormatter $formatter
     * @throws \LogicException
     */
    public function __construct(TextApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.virtualBoxing');

        $this->middleware('input.xml')->except(['error']);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $method = $request->input('name', $request->input('type', 'error'));

        $this->addMetaField('method', $method);

        $method = (string)S::camelize($method);

        if (method_exists($this, $method) && !S::isBlank($method)) {
            return app()->call([$this, $method], $request->all());
        } else {
            return $this->emptyResponse();
        }

        return app()->call([$this, 'error'], $request->all());
    }

    public function matchBet(MatchBetRequest $request)
    {
        $vbEventId = (int) $request->input('match.scheduleId');

        if(EventLink::isExists($vbEventId)) {
           return $this->respond(200, CodeMappingVirtualSports::DONE_DUPLICATE);
        }

        $eventProcessor = new EventProcessor();

        $dataMap = new DataMapper($request->all(), 'box');

        $created = $eventProcessor->create($dataMap);

        if(!$created) {
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::EVENT_NOT_FOUND));
        }

        //creates event, markets, categories, outcomes and in-its base results with all ok calculator message
        return $this->respondSuccess(null, [
            $eventProcessor->getEventId(),
            (int) $request->input('match.scheduleId')
        ]);
    }

    public function matchProgress(MatchProgressRequest $request)
    {
        $processor = EventProcessor::getEvent((int) $request->input('event_id'));

        switch ($request->input('xu:ups-at.xu:at.0.#text')) {
            case 'N':
                $processor->stopBets();
                break;
            case 'Z':
                $processor->finish();
                break;
            case 'V':
                $processor->cancel();
                break;
            default:
                break;
        }

        return $this->respondSuccess(null, [
            $processor->getEventId(),
            (int) $request->input('event_id')
        ]);
    }

    public function result(ResultRequest $request)
    {
        $processor = EventProcessor::getEvent((int)$request->input('result.event_id'));

        if(! Result::existsById($request->input('result.tid'))) {

            $dataMap = new DataMapper(array_get($request->input(), 'result', []), 'box');

            $processor->setResult($dataMap, false);

            Result::create(['tid' => $request->input('result.tid')]);

        } else {
            return $this->respondSuccess('This is duplicate', [
                $processor->getEventId(),
                (int) $request->input('result.event_id'),
                $request->input('result.tid')
            ]);
        }

        return $this->respondSuccess(null, [
            $processor->getEventId(),
            (int) $request->input('result.event_id'),
            $request->input('result.tid')
        ]);
    }

    public function emptyResponse()
    {
        $this->respond(200, '');
    }

    /**
     * @return Response
     */
    public function error()
    {
        throw new ApiHttpException(400, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::METHOD_NOT_FOUND));
    }


    /**
     * @param string $massage
     * @param array $payload
     * @return Response
     */
    public function respondSuccess(string $massage = null, array $payload)
    {
        $data = [
            'message'  => $massage ? : 'Done',
            'method'   => 'f_'.$this->getMetaField('method'),
            'response' => implode(':', $payload)
        ];

        return $this->respond(Response::HTTP_OK, '', $data);
    }
}
