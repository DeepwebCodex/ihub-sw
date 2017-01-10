<?php

namespace App\Models\Trans;

use Illuminate\Support\Collection;

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

    protected $translations;

    protected $languages = [
        'ru',
        'en',
        'uk'
    ];

    public function __construct()
    {
        $this->translations = new Collection();

        parent::__construct();
    }

    protected function mapTranslations(string $originalName) : string
    {
        $translatedName = transliterate($originalName);

        foreach ($this->languages as $language)
        {
            switch ($language)
            {
                case 'ru':
                    $this->translations->push([
                        'lang'  => $language,
                        'key'   => $translatedName,
                        'value' => $originalName
                    ]);
                    break;
                case 'en':
                    $this->translations->push([
                        'lang'  => $language,
                        'key'   => $translatedName,
                        'value' => $translatedName
                    ]);
                    break;
                case 'uk':
                    $this->translations->push([
                        'lang'  => $language,
                        'key'   => $translatedName,
                        'value' => $translatedName
                    ]);
                    break;
                default:
                    break;
            }
        }

        return $translatedName;
    }

    public function translate(string $name) : string
    {
        if($this->isNonLatin($name)) {
            $translatedName = $this->mapTranslations($name);

            $this->translations->each(function ($translation) {
                if (!static::where(['key' => $translation['key'], 'lang' => $translation['lang']])->exists()) {
                    static::create($translation);
                } else {
                    static::update($translation);
                }
            });

            return $translatedName;
        }

        return $name;
    }

    protected function isNonLatin(string $name) : bool
    {
        return preg_match('/[^\\p{Common}\\p{Latin}]/u', $name);
    }

    /**
     * @param array $options
     * @return bool
     */
    public function saveAs(array $options = [])
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
