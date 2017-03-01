<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\Templates\GameSessionTemplate;
use App\Http\Requests\GameSession\SessionCreateRequest;
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
        $input = Input::only('user_id', 'partner_id', 'game_id', 'currency', 'unique_id', 'cashdesk_id');

        $sessionId = app('GameSession')->create($input, 'md5');

        return $this->respondOk(Response::HTTP_OK, 'success', ['token' => $sessionId]);
    }

    public function error()
    {
        $this->respond(Response::HTTP_BAD_REQUEST, 'Bad request');
    }
}
