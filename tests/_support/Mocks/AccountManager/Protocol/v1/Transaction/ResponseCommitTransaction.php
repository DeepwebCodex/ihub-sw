<?php

namespace Testing\AccountManager\Protocol\v1\Transaction;

use Testing\AccountManager\Protocol\v1\DefaultParams;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResponseCreateTransaction
 *
 * @author petroff
 */
class ResponseCommitTransaction extends BaseResponseTransaction
{

    public function getProtocol(array $params): array
    {
        $base = parent::getProtocol($params);
        $base["status"] = DefaultParams::COMPLETED;
        return $base;
    }

}
