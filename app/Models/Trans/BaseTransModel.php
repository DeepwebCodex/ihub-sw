<?php

namespace App\Models\Trans;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseTransModel
 * @package App\Models\Trans
 */
abstract class BaseTransModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'trans';
}
