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
     * @param string $id
     * @return bool
     */
    public static function existsById(string $id)
    {
        return static::where('tid', $id)->exists();
    }
}
