<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Erlybet\Integration\GoldenRaceTransactionCashdesk;
use App\Repositories\GoldenRaceReportsRepository;
use App\Transformers\Internal\GoldenRace\HrCashdeskCardTransformer;
use App\Transformers\Internal\GoldenRace\UaCashdeskCardTransformer;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\ArraySerializer;

/**
 * Class GoldenRaceController
 * @package App\Http\Controllers\Internal
 */
class GoldenRaceController extends Controller
{
    const PARTNERS = [
        'ua' => [1, 18],
        'hr' => [50]
    ];

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReport()
    {
        $validator = Validator::make(Input::all(), [
            'partner_id' => 'bail|required',
            'from' => 'bail|required|date_value',
        ]);
        if ($validator->fails()) {
            return $this->wrongInputResponse();
        }

        $partnerId = Input::get('partner_id');
        $from = get_formatted_date(Input::get('from'));

        $reportsRepository = new GoldenRaceReportsRepository();

        $reportsInfo = [
            'report1' => $reportsRepository->getReport1($partnerId, $from),
            'report2' => $reportsRepository->getReport2($partnerId, $from),
            'report3_1' => $reportsRepository->getReport31($partnerId, $from),
            'report3_2' => $reportsRepository->getReport32($partnerId, $from),
            'report3_3' => $reportsRepository->getReport33($partnerId, $from),
        ];

        return $this->makeResponse(true, $reportsInfo);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReportCashdesk()
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

        $partnerId = (int)request()->server('PARTNER_ID');
        if (\in_array($partnerId, \array_flatten(self::PARTNERS), true)) {
            $states = explode(',', Input::get('states'));
            $states = array_map('trim', $states);

            $cardsInfo = (new GoldenRaceTransactionCashdesk())
                ->getCards(
                    $states,
                    Input::get('cashdesk_id'),
                    get_formatted_date(Input::get('from')),
                    get_formatted_date(Input::get('to')),
                    $partnerId
                );
            if ($cardsInfo) {
                $cardsInfo = fractal()
                    ->collection($cardsInfo, $this->getCardTransformer($partnerId))
                    ->serializeWith(new ArraySerializer())
                    ->toArray();
            }
        } else {
            return $this->wrongPartnerResponse();
        }

        $status = $cardsInfo !== null;

        return $this->makeResponse($status, $cardsInfo);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCardCashdesk()
    {
        $validator = Validator::make(Input::all(), [
            'barcode' => 'bail|required',
            'cashdesk_id' => 'bail|required',
        ]);
        if ($validator->fails()) {
            return $this->wrongInputResponse();
        }

        $partnerId = (int)request()->server('PARTNER_ID');
        if (\in_array($partnerId, \array_flatten(self::PARTNERS), true)) {
            $cardInfo = (new GoldenRaceTransactionCashdesk())
                ->getCard(
                    Input::get('barcode'),
                    Input::get('cashdesk_id'),
                    $partnerId
                );
            if ($cardInfo) {
                $cardInfo = fractal()
                    ->item($cardInfo, $this->getCardTransformer($partnerId))
                    ->serializeWith(new ArraySerializer())
                    ->toArray();
            }
        } else {
            return $this->wrongPartnerResponse();
        }
        $status = $cardInfo !== null;

        return $this->makeResponse($status, $cardInfo);
    }

    /**
     * @param int $partnerId
     * @return TransformerAbstract
     */
    protected function getCardTransformer($partnerId)
    {
        foreach (self::PARTNERS as $lang => $partnersIdList) {
            if (\in_array($partnerId, $partnersIdList, true)) {
                $className = ucfirst($lang) . 'CashdeskCardTransformer';
                if (class_exists($className)) {
                    return new $className();
                }
                break;
            }
        }
        return new UaCashdeskCardTransformer();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function wrongPartnerResponse()
    {
        return $this->makeResponse(false, 'Not match partner id');
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
