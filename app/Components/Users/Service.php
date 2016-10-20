<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 5:50 PM
 */

namespace App\Components\Users;

/**
 * @property integer $user_id
 * @property integer $service_id
 * @property string  $service_name
 * @property integer $payment_instrument_id
 * @property string  $wallet_id
 * @property string  $wallet_account_id
 * @property boolean $public
 * @property boolean $is_enabled
 * @property integer $delay
 * @property float   $limit_min
 * @property float   $limit_max
 * @property boolean $is_blocked
 * @property string  $block_text
 * @property integer $flags
 * @property integer $bit_params
 * @property string  $currency
 */
class Service
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