<?php

namespace App\Models\VirtualBoxing;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseVirtualBoxingModel
 * @package App\Models\Line
 */
abstract class BaseVirtualBoxingModel extends Model
{
    const DB_SCHEMA = 'vb';

    /**
     * {@inheritdoc}
     */
    protected $connection = 'line';

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return self::DB_SCHEMA . '.' . parent::getTable();
    }
}
