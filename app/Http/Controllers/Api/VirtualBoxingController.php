<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\TextApiFormatter;
use App\Components\Integrations\VirtualBoxing\BetService;
use App\Components\Integrations\VirtualBoxing\ProgressService;
use App\Components\Integrations\VirtualBoxing\ResultService;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\Templates\VirtualBoxingTemplate;
use App\Facades\AppLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Stringy\StaticStringy as S;

/**
 * Class VirtualBoxingController
 * @package App\Http\Controllers\Api
 */
class VirtualBoxingController extends BaseApiController
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

        $this->addMetaField('source_input', $this->getRequest()->getContent());
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $method = $request->input('name', $request->input('type', 'error'));

        $method = (string)S::camelize($method);

        $this->addMetaField('method', $method);

        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function matchBet(Request $request)
    {
        $validator = Validator::make(Input::all(), [
            'match.scheduleId' => 'bail|required|numeric',
            'match.competition' => 'bail|required|string',
            'match.bet' => 'bail|required|array',
            'match.away' => 'bail|required|string',
            'match.home' => 'bail|required|string',
            'match.location' => 'bail|required|string',
            'match.date' => 'bail|required|date_format:Y-m-d',
            'match.time' => 'bail|required|date_format:H:i:s',
            'match.name' => 'bail|required|string',
        ]);
        if ($validator->fails()) {
            $message = $this->getMessageByCode('miss_element');
            AppLog::error($message);
            return $this->respondError($message);
        }

        $betService = new BetService($this->options);
        try {
            $betService->setBet($request->input('match'));
        } catch (\Exception $e) {
            $errorCode = $e->getMessage();
            $errorMessage = $this->getMessageByCode($errorCode);
            $this->respondError($errorMessage);
        }

        $message = $this->getMessageByCode('done') . ' ' . $this->getMetaField('method') . ' '
            . $betService->getEventId() . ':' . $betService->getEventVbId();

        return $this->respondSuccess($message);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function matchProgress(Request $request)
    {
        $validator = Validator::make(Input::all(), [
            'event_id' => 'bail|required|numeric',
            'mnem' => 'bail|required|in:MB',
            'xu:ups-at.xu:at' => 'bail|required|array'
        ]);

        $progressName = $request->input('xu:ups-at.xu:at')[0]['#text'];
        if ($validator->fails()
            || !in_array($progressName, ['N', 'Z', 'V'], true)
        ) {
            $message = $this->getMessageByCode('miss_element');
            AppLog::error($message);
            return $this->respondError($message);
        }

        $eventVbId = (int)$request->input('event_id');

        $progressService = new ProgressService($this->options);
        try {
            $progressService->setProgress($eventVbId, $this->getOption('sport_id'), $progressName);
        } catch (\Exception $e) {
            $errorCode = $e->getMessage();
            $errorMessage = $this->getMessageByCode($errorCode);
            $this->respondError($errorMessage);
        }

        $message = $this->getMessageByCode('done') . ' ' . $this->getMetaField('method') . ' '
            . $progressService->getEventId() . ':' . $eventVbId;

        return $this->respondSuccess($message);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function result(Request $request)
    {
        $validator = Validator::make(Input::all(), [
            'result.event_id' => 'bail|required|numeric',
            'result.tid' => 'bail|required|string',
            'result.round' => 'bail|required|array',
        ]);
        if ($validator->fails()) {
            $message = $this->getMessageByCode('miss_element');
            AppLog::error($message);
            return $this->respondError($message);
        }

        $eventVbId = (int)$request->input('result.event_id');
        $tid = $request->input('result.tid');
        $rounds = $request->input('result.round');

        $resultService = new ResultService($this->options);
        try {
            $resultService->setResult($eventVbId, $tid, $rounds);
        } catch (\Exception $e) {
            $errorCode = $e->getMessage();
            $errorMessage = $this->getMessageByCode($errorCode);
            $this->respondError($errorMessage);
        }

        $message = $this->getMessageByCode('done') . ' ' . $this->getMetaField('method') . ' '
            . $resultService->getEventId() . ':' . $eventVbId . ':' . $tid;

        return $this->respondSuccess($message);
    }

    /**
     * @return Response
     */
    public function error()
    {
        return $this->respondError($this->getMessageByCode('error_method_not_found'));
    }

    /**
     * @param $messageCode
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getMessageByCode($messageCode)
    {
        return trans("api.vb.{$messageCode}");
    }

    /**
     * @param string $message
     * @return Response
     */
    public function respondSuccess(string $message)
    {
        return $this->respond(Response::HTTP_OK, $message);
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\Response
     */
    protected function respondError(string $message)
    {
        return $this->respond(Response::HTTP_BAD_REQUEST, $message);
    }
}
