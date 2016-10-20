<?php

namespace App\Models\VirtualBoxing;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseLineModel
 * @package App\Models\Line
 */
abstract class BaseVirtualBoxingModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'lineVirtualBoxing';
}
