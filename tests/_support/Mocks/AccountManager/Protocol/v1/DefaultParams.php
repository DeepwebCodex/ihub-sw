<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Testing\AccountManager\Protocol\v1;

use function env;

/**
 * @deprecated
 * Description of DefaultParams
 *
 * @author petroff
 */
final class DefaultParams
{

    const CASHDESK_ID = -5;
    const __RECORD = 'operation';
    const PAYMENT_INSTRUMENT_ID = 123;
    const WALLET_ID = 123;
    const WALLET_ACCOUNT_ID = 123;
    const PARTNER_ID = 1;
    const CLIENT_IP = '127.0.0.1';
    const COMMENT = 'Default coment';
    const SERVICE_ID = 13;
    const MOVE = 1;
    const AMOUNT = 10;
    const CURRENCY = 'EUR';
    const DEPOSIT_REST = 10;
    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const AMOUNT_BALANCE = 1000;

    public $dt;
    public $dt_done;
    public $operation_id;
    public $user_id;
    public $object_id;
    private static $instance;

    function __construct()
    {
        $this->dt = date("Y-m-d H:i:s");
        $this->dt_done = date("Y-m-d H:i:s");
        $this->user_id = env('TEST_USER_ID');
        $this->object_id = $this->getUniqueId();
        $this->operation_id = $this->getUniqueId();
    }

    public function getUniqueId()
    {
        return round(microtime(true)) + mt_rand(1, 10000);
    }

    public static function get()
    {
        if (self::$instance) {
            return self::$instance;
        } else {
            self::$instance = new DefaultParams();
            return self::$instance;
        }
    }

}
