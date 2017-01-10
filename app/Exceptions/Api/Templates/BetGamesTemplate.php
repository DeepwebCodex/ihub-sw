<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\StatusCode;
use App\Components\Transactions\TransactionHelper;

class BetGamesTemplate implements IExceptionTemplate
{
    const TIME_TO_DISCONNECT = 10;

    private $code;
    private $token;
    private $method;
    private $balance;
    private $statusCode;
    private $errorCode;
    private $errorMessage;
    private $time_to_disconnect;

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

        $view = [
            'method' => $this->method,
            'token' => $this->token,
            'success' => 0,
            'error_code' => $this->errorCode,
            'error_text' => $this->errorMessage,
            'time' => time(),
        ];
        $view['signature'] = (new Signature($view))->getHash();

        return $view;
    }

    private function initialize(array $item, $statusCode, bool $isApiException = false)
    {
        $this->code = $item['code'] ?? StatusCode::UNKNOWN;
        $this->token = $item['token'] ?? '';
        $this->method = $item['method'] ?? '';
        $this->balance = $item['balance'] ?? null;
        $this->statusCode = $statusCode ?? 500;

        $error = CodeMapping::getByErrorCode($this->code);
        $this->errorCode = $error['code'] ?? null;
        $message = ($isApiException && isset($item['message'])) ? $item['message'] : null;
        $this->errorMessage = $message ?? $error['message'] ?? '';

        $this->time_to_disconnect = env('BETGAMES_DISCONNECT_TIME', self::TIME_TO_DISCONNECT);
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
        $error = CodeMapping::getByErrorCode(StatusCode::UNKNOWN);
        sleep($this->time_to_disconnect);
        $view = [
            'method' => $this->method,
            'token' => $this->token,
            'success' => 0,
            'error_code' => $error['code'],
            'error_text' => $error['message'],
            'time' => time(),
        ];
        $view['signature'] = (new Signature($view))->getHash();

        return $view;
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

        $error = CodeMapping::getByErrorCode(StatusCode::OK);
        $view = [
            'method' => $this->method,
            'token' => $this->token,
            'success' => 1,
            'error_code' => $error['code'],
            'error_text' => $error['message'],
            'time' => time(),
            'params' => [
                'balance_after' => $this->balance,
                'already_processed' => 1
            ]
        ];
        $view['signature'] = (new Signature($view))->getHash();

        return $view;
    }
}