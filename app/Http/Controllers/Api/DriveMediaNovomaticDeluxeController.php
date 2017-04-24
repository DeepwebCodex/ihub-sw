<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\DriveMediaNovomaticDeluxeApiFormatter;
use iHubGrid\ErrorHandler\Http\CodeMappingBase;
use App\Components\Integrations\DriveMediaNovomaticDeluxe\CodeMapping;
use App\Components\Integrations\DriveMediaNovomaticDeluxe\Operation;
use iHubGrid\ErrorHandler\Http\Controllers\Api\BaseApiController;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\DriveMediaNovomaticDeluxeTemplate;
use App\Http\Requests\DriveMediaNovomaticDeluxe\GetBalanceRequest;
use App\Http\Requests\DriveMediaNovomaticDeluxe\WriteBetRequest;
use App\Http\Requests\Validation\DriveMedia\NovomaticDeluxeValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use function app;
use function config;

/**
 * Class DriveMediaNovomaticDeluxeController
 * @package App\Http\Controllers\Api
 */
class DriveMediaNovomaticDeluxeController extends BaseApiController {

    use MetaDataTrait;

    /** @var string  */
    public static $exceptionTemplate = DriveMediaNovomaticDeluxeTemplate::class;

    private $userId;
    private $login;

    /**
     * DriveMediaNovomaticDeluxeController constructor.
     * @param DriveMediaNovomaticDeluxeApiFormatter $formatter
     */
    public function __construct(DriveMediaNovomaticDeluxeApiFormatter $formatter) {
        parent::__construct($formatter);
        $this->options = config('integrations.DriveMediaNovomaticDeluxe');

        $this->middleware('check.ip:DriveMediaNovomaticDeluxe');
        $this->middleware('input.json')->except(['error']);
        $this->middleware('input.dm.parselogin')->except(['error']);

        Validator::extend('validate_space', 'App\Http\Requests\Validation\DriveMedia\NovomaticDeluxeValidation@validateSpace');
        Validator::extend('check_sign', 'App\Http\Requests\Validation\DriveMedia\NovomaticDeluxeValidation@checkSign');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {
        $method = $request->input('cmd');
        $this->userId = $request->input('userId');

        $this->setMetaData([
            'imprint' => $request->all()
        ]);

        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }
        return app()->call([$this, 'error'], $request->all());
    }

    public function error() {
        throw new ApiHttpException(404, 'Unknown method', CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD));
    }

    /**
     * @param GetBalanceRequest $request
     * @return Response
     */
    public function getBalance(GetBalanceRequest $request) {
        $user = IntegrationUser::get($this->userId, $this->getOption('service_id'), 'DriveMediaNovomaticDeluxe');

        NovomaticDeluxeValidation::checkCurrency($user->getCurrency(), $request->input('space'));

        return $this->respondOk(200, '', [
                    'login' => $request->input('login'),
                    'balance' => $user->getBalance(),
        ]);
    }

    /**
     * @param WriteBetRequest $request
     * @return Response
     */
    public function writeBet(WriteBetRequest $request) {
        $user = IntegrationUser::get($this->userId, $this->getOption('service_id'), 'DriveMediaNovomaticDeluxe');

        NovomaticDeluxeValidation::checkCurrency($user->getCurrency(), $request->input('space'));

        $operation = new Operation();

        $operation->process($user, $request, $this->getOption('service_id'));

        return $this->respondOk(200, '', [
                    'login' => $request->input('login'),
                    'balance' => $operation->getBalance(),
                    'operationId' => $operation->getOperationId()
        ]);
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @param array $payload
     * @return Response
     */
    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = array()): Response {
        $base = [
            'status' => CodeMappingBase::SUCCESS,
            'error' => '',
        ];
        $view = array_merge($base, $payload);

        return parent::respondOk($statusCode, $message, $view);
    }

}
