<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 5:49 PM
 */

namespace App\Components\Users;

/**
 * @property integer $user_id
 * @property integer $payment_instrument_id
 * @property string  $payment_instrument_name
 * @property string  $wallet_id
 * @property string  $wallet_account_id
 * @property integer $partner_id
 * @property string  $currency
 * @property boolean $is_default
 * @property boolean $is_active
 * @property float   $deposit
 * @property string  $creation_date
 * @property float   $payment_instrument_transfer_time
 * @property integer $cashdesk
 */
class Wallet
{
    private $attributes = [];

    public function __construct(array $data)
    {
        $this->attributes = $data;
    }

    public function __get($name)
    {
        return array_get($this->attributes, $name);
    }
}