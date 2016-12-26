<?php

namespace App\Models\VirtualBoxing;

/**
 * Class Result
 * @package App\Models\VirtualBoxing
 */
class Result extends BaseVirtualBoxingModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'result_vb';

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public $fillable = ['tid'];

    /**
     * @param string $tid
     * @return bool
     */
    public static function existsById(string $tid)
    {
        return static::where('tid', $tid)->exists();
    }
}
