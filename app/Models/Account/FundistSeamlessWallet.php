<?php

namespace App\Models\Account;

/**
 * Class FundistSeamlessWallet
 * @package App\Models\Account
 */
class FundistSeamlessWallet extends BaseAccountModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'fundist_seamless_wallets';

    /**
     * @param $userId
     * @param $currency
     * @return mixed
     */
    public static function checkExistsByUserId($userId, $currency)
    {
        return static::where('user_id', '=', $userId)
            ->where('currency', '=', $currency)
            ->exists();
    }
}
