<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\ResponseData;
use App\Components\Integrations\BetGames\StatusCode;
use App\Components\Transactions\TransactionHelper;

class BetGamesTemplate implements IExceptionTemplate
{
    private $code;
    private $token;
    private $method;
    private $balance;
    private $statusCode;
    private $isApiException;
    private $errorCode;
    private $errorMessage;

    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return mixed
     */
    public function mapping($item, $statusCode, $isApiException):array
    {
        $this->initialize($item, $statusCode, $isApiException);

        if ($this->isDuplicateWin()) {
            return $this->onDuplicateWin();
        }

        /** internal server and timeout error cases.
         * Must be after 'onDuplicateWin', because 500 "DuplicateWin" error is also internal server error */
        if ($this->isUnknownError()) {
            return $this->onUnknownError();
        }

        $data = new ResponseData($this->method, $this->token, [], ['code' => $this->errorCode, 'message' => $this->errorMessage]);
        return $data->fail();
    }

    private function initialize(array $item, $statusCode, bool $isApiException)
    {
        $this->code = $item['code'] ?? StatusCode::UNKNOWN;
        $this->token = $item['token'] ?? '';
        $this->method = $item['method'] ?? '';
        $this->balance = $item['balance'] ?? null;
        $this->statusCode = $statusCode ?? 500;
        $this->isApiException = $isApiException ?? false;

        $error = CodeMapping::getByErrorCode($this->code);
        $this->errorCode = $error['code'] ?? null;
        $this->errorMessage = $error['message'] ?? '';
    }

    /**
     * Internal server error or timeout
     *
     * @return bool
     */
    private function isUnknownError():bool
    {
        return in_array($this->statusCode, [500, 503, 504]) && is_null(TransactionHelper::getTransactionErrorCode($this->code));
    }

    /**
     * @return array
     */
    private function onUnknownError():array
    {
        $response = new ResponseData($this->method, $this->token, [], CodeMapping::getByErrorCode(StatusCode::UNKNOWN));
        return $response->fail(true);
    }

    /**
     * @return bool
     */
    private function isDuplicateWin():bool
    {
        return ($this->method == 'transaction_bet_payout') && ($this->code == StatusCode::BAD_OPERATION_ORDER);
    }

    /**
     * @return array
     */
    private function onDuplicateWin():array
    {
        app('GameSession')->prolong($this->token);
        $data = new ResponseData($this->method, $this->token, [
            'balance_after' => $this->balance,
            'already_processed' => 1
        ], CodeMapping::getByErrorCode(StatusCode::OK));

        return $data->ok();
    }
}