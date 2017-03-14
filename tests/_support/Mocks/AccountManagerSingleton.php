<?php


namespace Testing;

class AccountManagerSingleton
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getObject(
        $serviceId,
        $amount = Params::AMOUNT,
        $currency = Params::CURRENCY
    ) {
        if (self::$instance) {
            return self::$instance;
        } else {
            self::$instance = (new AccountManagerMock($serviceId, $amount,
                $currency))->getMock();
            return self::$instance;
        }

    }
}