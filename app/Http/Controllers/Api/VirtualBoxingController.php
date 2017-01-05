<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\TextApiFormatter;
use App\Components\Integrations\VirtualBoxing\BetService;
use App\Components\Integrations\VirtualBoxing\ProgressService;
use App\Components\Integrations\VirtualBoxing\ResultService;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\Templates\VirtualBoxingTemplate;
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

    const BET_VALIDATION_RULES = [
        'match.scheduleId' => 'bail|required|numeric',
        'match.competition' => 'bail|required|string',
        'match.bet' => 'bail|required|array',
        'match.away' => 'bail|required|string',
        'match.home' => 'bail|required|string',
        'match.location' => 'bail|required|string',
        'match.date' => 'bail|required|date_format:Y-m-d',
        'match.time' => 'bail|required|date_format:H:i:s',
        'match.name' => 'bail|required|string',
    ];

    const PROGRESS_VALIDATION_RULES = [
        'event_id' => 'bail|required|numeric',
        'mnem' => 'bail|required|in:MB',
    ];

    const RESULT_VALIDATION_RULES = [
        'result.event_id' => 'bail|required|numeric',
        'result.tid' => 'bail|required|string',
        'result.round' => 'bail|required|array',
    ];

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
        $validator = Validator::make(Input::all(), self::BET_VALIDATION_RULES);
        if ($validator->fails()) {
            $responseMessage = $this->getMessageDescription('miss_element');
            return $this->respondError($responseMessage);
        }

        $betService = new BetService($this->options);
        try {
            $betService->setBet($request->input('match'));
        } catch (\Exception $exception) {
            return $this->processException($exception);
        }

        $responseMessage = $betService->getEventId() . ':' . $betService->getEventVbId();

        return $this->respondSuccess($responseMessage);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function matchProgress(Request $request)
    {
        $validator = Validator::make(Input::all(), self::PROGRESS_VALIDATION_RULES);

        $statusCode = Input::get('xu:ups-at.xu:at')[0]['#text'];
        if ($validator->fails()) {
            $responseMessage = $this->getMessageDescription('miss_element');
            return $this->respondError($responseMessage);
        }

        $eventVbId = (int)$request->input('event_id');

        try {
            $progressService = new ProgressService($this->options, $eventVbId);
            $progressService->setProgress($statusCode);
        } catch (\Exception $exception) {
            return $this->processException($exception);
        }

        $responseMessage = $progressService->getEventId() . ':' . $eventVbId;

        return $this->respondSuccess($responseMessage);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function result(Request $request)
    {
        $validator = Validator::make(Input::all(), self::RESULT_VALIDATION_RULES);
        if ($validator->fails()) {
            $responseMessage = $this->getMessageDescription('miss_element');
            return $this->respondError($responseMessage);
        }

        $eventVbId = (int)$request->input('result.event_id');
        $tid = $request->input('result.tid');
        $rounds = $request->input('result.round');

        $resultService = new ResultService($this->options, $eventVbId);
        try {
            $resultService->setResult($tid, $rounds);
        } catch (\Exception $exception) {
            return $this->processException($exception);
        }

        $responseMessage = $resultService->getEventId() . ':' . $eventVbId . ':' . $tid;

        return $this->respondSuccess($responseMessage);
    }

    /**
     * @return Response
     */
    public function error()
    {
        return $this->respondError($this->getMessageDescription('error_method_not_found'));
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    protected function processException(\Exception $exception)
    {
        $errorMessageCode = $exception->getMessage();
        $errorMessage = $this->getMessageDescription($errorMessageCode);
        return $this->respondError($errorMessage, (int)$exception->getCode());
    }

    /**
     * @param string $message
     * @return string
     */
    protected function getMessageDescription(string $message):string
    {
        $errorKey = "api_virtual_boxing.{$message}";
        $errorDescription = trans($errorKey);
        return $errorDescription !== $errorKey ? $errorDescription : $message;
    }

    /**
     * @param string $message
     * @return Response
     */
    public function respondSuccess(string $message)
    {
        $message = $this->getMessageDescription('done') . ' ' . $this->getMetaField('method') . ' ' . $message;
        return $this->respond(Response::HTTP_OK, $message);
    }

    /**
     * @param string $message
     * @param int $code
     * @return Response
     */
    protected function respondError(string $message, int $code = Response::HTTP_BAD_REQUEST)
    {
        if ($code === 0) {
            $code = Response::HTTP_BAD_REQUEST;
        }
        return $this->respond($code, $message);
    }
}
