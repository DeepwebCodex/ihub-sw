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
    protected $table = 'vb.result_vb';

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
