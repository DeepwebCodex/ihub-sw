<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseAccountModel
 * @package App\Models\Account
 */
abstract class BaseAccountModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'account';
}
