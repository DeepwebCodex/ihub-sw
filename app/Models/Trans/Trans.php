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
     * {@inheritdoc}
     */
    public $fillable = ['key', 'value', 'lang'];

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        foreach ($options as $lang => $value) {
            $key = trim($value['key']);
            $value = trim($value['value']);

            $recordExist = static::where([
                'key' => $key,
                'lang' => $lang
            ])->exists();

            if (!$recordExist) {
                static::create([
                    'key' => $key,
                    'value' => $value,
                    'lang' => $lang
                ]);
            }
            \DB::connection($this->connection)
                ->table($this->table)
                ->where(['key' => $key, 'lang' => $lang])
                ->update(['value' => $value]);
        }
        return true;
    }
}
