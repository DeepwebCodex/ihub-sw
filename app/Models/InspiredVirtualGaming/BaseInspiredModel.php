<?php

namespace App\Models\InspiredVirtualGaming;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseVirtualBoxingModel
 * @package App\Models\Line
 */
abstract class BaseInspiredModel extends Model
{
    const DB_SCHEMA = 'ivg';

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
