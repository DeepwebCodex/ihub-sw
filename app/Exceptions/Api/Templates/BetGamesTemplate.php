<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\ResponseData;
use App\Components\Integrations\BetGames\StatusCode;

class BetGamesTemplate implements IExceptionTemplate
{
    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return mixed
     */
    public function mapping($item, $statusCode, $isApiException)
    {
        $error = CodeMapping::getByErrorCode($item['code']);

        //internal server and timeout error cases
        if ($this->isInternalError($statusCode, $error['code'])) {
            return $this->onInternalError($item);
        }

        if ($this->isDuplicateWin($item)) {
            return $this->onDuplicateWin($item);
        }

        $data = new ResponseData($item['method'], $item['token'], [], $error);
        return $data->fail();
    }

    /**
     * Internal server error or timeout
     *
     * @param $statusCode
     * @param $errorCode
     * @return bool
     */
    private function isInternalError($statusCode, $errorCode)
    {
        return in_array($statusCode, [500, 503, 504]) || is_null($errorCode);
    }

    private function onInternalError($item)
    {
        $response = new ResponseData($item['method'], $item['token'], [], CodeMapping::getByErrorCode(StatusCode::UNKNOWN));
        return $response->fail(true);
    }

    /**
     * @param $data
     * @return bool
     */
    private function isDuplicateWin($data)
    {
        return ($data['method'] == 'transaction_bet_payout') && ($data['code'] == StatusCode::BAD_OPERATION_ORDER);
    }

    /**
     * @param $item
     * @return mixed
     */
    private function onDuplicateWin($item)
    {
        app('GameSession')->prolong($item['token']);
        $data = new ResponseData($item['method'], $item['token'], [
            'balance_after' => $item['balance'],
            'already_processed' => 1
        ], CodeMapping::getByErrorCode(StatusCode::OK));

        return $data->ok();
    }
}