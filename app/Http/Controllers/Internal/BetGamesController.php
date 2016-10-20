<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Erlybet\CardsBetGames;
use App\Transformers\Internal\BetGames\CashdeskCardsTransformer;
use App\Transformers\Internal\BetGames\CashdeskCardTransformer;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Spatie\Fractal\ArraySerializer;

/**
 * Class BetGamesController
 * @package App\Http\Controllers\Internal
 */
class BetGamesController extends Controller
{
    const NODE = 'bet_games';

    /**
     * BetGamesController constructor.
     */
    public function __construct()
    {
        Validator::extend('date_value', 'App\Http\Requests\Validation\GlobalValidation@validateDateValue');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function cashdeskCard()
    {
        $validator = Validator::make(Input::all(), [
            'barcode' => 'bail|required',
            'cashdesk_id' => 'bail|required',
        ]);
        if ($validator->fails()) {
            return $this->wrongInputResponse();
        }

        $cardInfo = (new CardsBetGames)->getCard(
            Input::get('barcode'),
            Input::get('cashdesk_id')
        );
        if ($cardInfo) {
            $cardInfo = fractal()
                ->item($cardInfo, new CashdeskCardTransformer())
                ->serializeWith(new ArraySerializer())
                ->toArray();
        }
        $status = $cardInfo !== null;

        return $this->makeResponse($status, $cardInfo);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function cashdeskCards()
    {
        $validator = Validator::make(Input::all(), [
            'states' => 'bail|required',
            'cashdesk_id' => 'bail|required',
            'from' => 'bail|required|date_value',
            'to' => 'bail|required|date_value',
        ]);
        if ($validator->fails()) {
            return $this->wrongInputResponse();
        }

        $states = explode(',', Input::get('states'));
        $states = array_map('trim', $states);

        $cardsInfo = (new CardsBetGames)->getCards(
            $states,
            Input::get('cashdesk_id'),
            get_formatted_date(Input::get('from')),
            get_formatted_date(Input::get('to'))
        );

        if ($cardsInfo) {
            $cardsInfo = fractal()
                ->collection($cardsInfo, new CashdeskCardsTransformer())
                ->serializeWith(new ArraySerializer())
                ->toArray();
        }
        $status = $cardsInfo !== null;

        return $this->makeResponse($status, $cardsInfo);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function wrongInputResponse()
    {
        return $this->makeResponse(false, 'Miss params');
    }

    /**
     * @param bool $status
     * @param mixed $msg
     * @return \Illuminate\Http\JsonResponse
     */
    protected function makeResponse($status, $msg)
    {
        return response()->json([
            'status' => $status,
            'msg' => $msg
        ]);
    }
}
