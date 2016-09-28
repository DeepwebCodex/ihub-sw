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

        return $this->makeResponse($reportsInfo, true);
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
        if ($this->checkPartnerId($partnerId) === false) {
            return $this->wrongPartnerResponse();
        }

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

        return $this->makeResponse($cardsInfo);
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
        if ($this->checkPartnerId($partnerId) === false) {
            return $this->wrongPartnerResponse();
        }

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

        return $this->makeResponse($cardInfo);
    }

    /**
     * @param int $partnerId
     * @return bool
     */
    protected function checkPartnerId($partnerId)
    {
        return \in_array($partnerId, \array_flatten($this->getPartnersByCountryList()), true);
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    protected function getPartnersByCountryList()
    {
        $value = config('integrations.goldenRace.partners_id_by_country');
        if (!$value || !is_array($value)) {
            throw new \RuntimeException;
        }
        return $value;
    }

    /**
     * @param int $partnerId
     * @return TransformerAbstract
     */
    protected function getCardTransformer($partnerId)
    {
        foreach ($this->getPartnersByCountryList() as $lang => $partnersIdList) {
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
        return $this->makeResponse('Not match partner id', false);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function wrongInputResponse()
    {
        return $this->makeResponse('Miss params', false);
    }

    /**
     * @param mixed $msg
     * @param bool $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function makeResponse($msg, $status = null)
    {
        return response()->json([
            'status' => $status ?? ($msg !== null),
            'msg' => $msg
        ]);
    }
}
