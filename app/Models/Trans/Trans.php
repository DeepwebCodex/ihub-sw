<?php

namespace App\Models\Trans;

/**
 * Class Trans
 * @package App\Models\Trans
 */
class Trans extends BaseTransModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'trans';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param array $arr
     * @return bool|int|static
     */
    public function save(array $arr)
    {
        foreach ($arr as $lang => $value) {
            $key = trim($value['key']);
            $value = trim($value['value']);

            $recordExist = static::where([
                'key' => $key,
                'lang' => $lang
            ])->exists();

            if (!$recordExist) {
                return static::create([
                    'key' => $key,
                    'value' => $value,
                    'lang' => $lang
                ]);
            }
            return \DB::connection($this->connection)
                ->table($this->table)
                ->where(['key' => $key, 'lang' => $lang])
                ->update(['value' => $value]);
        }
    }
}
