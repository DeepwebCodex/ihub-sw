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
        $input = [];

        if (!is_null(Input::get('user_id'))){
            $input['user_id'] = Input::get('user_id');
        }
        if (!is_null(Input::get('partner_id'))){
            $input['partner_id'] = Input::get('partner_id');
        }
        if (!is_null(Input::get('game_id'))){
            $input['game_id'] = Input::get('game_id');
        }
        if (!is_null(Input::get('currency'))){
            $input['currency'] = Input::get('currency');
        }
        if (!is_null(Input::get('unique_id'))){
            $input['unique_id'] = Input::get('unique_id');
        }
        if (!is_null(Input::get('cashdesk_id'))){
            $input['cashdesk_id'] = Input::get('cashdesk_id');
        }

        $sessionId = app('GameSession')->create($input);

        return $this->respondOk(Response::HTTP_OK, 'success', ['token' => $sessionId]);
    }

    public function error()
    {
        $this->respond(Response::HTTP_BAD_REQUEST, 'Bad request');
    }
}
