<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseLineModel
 * @package App\Models\Line
 */
abstract class BaseLineModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'line';
}
