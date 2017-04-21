<?php

namespace App\Http\Controllers\Api;

use iHubGrid\ErrorHandler\Formatters\JsonApiFormatter;
use iHubGrid\ErrorHandler\Http\Controllers\Api\BaseApiController;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use App\Exceptions\Api\Templates\GameSessionTemplate;
use App\Http\Requests\GameSession\SessionCreateRequest;
use App\Http\Requests\GameSession\SessionCreateWithContextRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

/**
 * Class GameSessionController
 * @package App\Http\Controllers\Api
 */
class GameSessionController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = GameSessionTemplate::class;

    /**
     * GameSessionController constructor.
     * @param JsonApiFormatter $formatter
     */
    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->middleware('input.json')->except(['error']);
    }

    /**
     * @param SessionCreateRequest $request
     * @return Response
     */
    public function create(SessionCreateRequest $request)
    {
        $input = Input::only(
            'user_id',
            'partner_id',
            'game_id',
            'currency',
            'unique_id',
            'cashdesk_id',
            'userIp',
            'context'
        );

        $sessionId = app('GameSession')->create($input, 'md5');

        return $this->respondOk(Response::HTTP_OK, 'success', ['token' => $sessionId]);
    }

    /**
     * @param SessionCreateWithContextRequest $request
     * @return Response
     */
    public function createWithContext(SessionCreateWithContextRequest $request)
    {
        $input = Input::only(
            'user_id',
            'partner_id',
            'game_id',
            'currency',
            'unique_id',
            'cashdesk_id',
            'userIp',
            'context'
        );

        $sessionId = app('GameSession')->createWithContext($input['context'], $input, 'md5');

        return $this->respondOk(Response::HTTP_OK, 'success', ['token' => $sessionId]);
    }

    /**
     * @return Response
     */
    public function error()
    {
        return $this->respond(Response::HTTP_BAD_REQUEST, 'Bad request');
    }
}
