<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Erlybet\CardsBgModel;
use App\Transformers\Internal\Bg\CashdeskCardsTransformer;
use App\Transformers\Internal\Bg\CashdeskCardTransformer;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Spatie\Fractal\ArraySerializer;

/**
 * Class BgController
 * @package App\Http\Controllers\Internal
 */
class BgController extends Controller
{
    const NODE = 'bg';

    /**
     * BgController constructor.
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

        $cardInfo = (new CardsBgModel)->getCard(Input::get('barcode'), Input::get('cashdesk_id'));
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

        $cardsInfo = (new CardsBgModel)->getCards(
            $states,
            Input::get('cashdesk_id'),
            Input::get('from'),
            Input::get('to')
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
