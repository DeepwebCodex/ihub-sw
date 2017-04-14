<?php

namespace Testing\AccountManager\Protocol\v1\Transaction;

use Exception;
use Testing\AccountManager\Protocol\v1\DefaultParams;
use Testing\AccountManager\Protocol\v1\ResponseInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseResponseTransaction
 *
 * @author petroff
 */
abstract class BaseResponseTransaction implements ResponseInterface
{

    public function getBase()
    {
        $default = DefaultParams::get();
        return [
            "__record" => DefaultParams::__RECORD,
            "operation_id" => $default->operation_id,
            "service_id" => DefaultParams::SERVICE_ID,
            "cashdesk" => DefaultParams::CASHDESK_ID,
            "user_id" => $default->user_id,
            "payment_instrument_id" => DefaultParams::PAYMENT_INSTRUMENT_ID,
            "wallet_id" => DefaultParams::WALLET_ID,
            "wallet_account_id" => DefaultParams::WALLET_ACCOUNT_ID,
            "partner_id" => DefaultParams::PARTNER_ID,
            "move" => DefaultParams::MOVE,
            "status" => null,
            "dt" => $default->dt,
            "dt_done" => $default->dt_done,
            "object_id" => $default->object_id,
            "amount" => DefaultParams::AMOUNT,
            "currency" => DefaultParams::CURRENCY,
            "client_ip" => DefaultParams::CLIENT_IP,
            "comment" => DefaultParams::COMMENT,
            "deposit_rest" => DefaultParams::DEPOSIT_REST,
        ];
    }

    public function getProtocol(array $params)
    {
        $params = $this->validation($params);
        $base = $this->getBase();
        return array_merge($base, $params);
    }

    public function validation(array $params): array
    {
        if (!isset($params['amount'])) {
            throw new Exception('amount must be fill');
        }
        
        if (!isset($params['deposit_rest'])) {
            throw new Exception('deposit_rest must be fill');
        }

        return $params;
    }

}
