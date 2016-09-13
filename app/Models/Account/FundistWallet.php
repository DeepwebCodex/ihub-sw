<?php

namespace App\Models\Account;

/**
 * Class FundistWallet
 * @package App\Models\Account
 */
class FundistWallet extends BaseAccountModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'fundist_wallets';

    /**
     * @param $userId
     * @return mixed
     */
    public static function checkExistsByUserId($userId)
    {
        return static::where('user_id', '=', $userId)
            ->exists();
    }
}