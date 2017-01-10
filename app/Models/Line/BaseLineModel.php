<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Model;


abstract class BaseLineModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'line';
}
