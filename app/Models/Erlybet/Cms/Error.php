<?php

namespace App\Models\Erlybet\Cms;

use App\Models\Erlybet\BaseErlybetModel;

/**
 * Class Error
 * @package App\Models\Erlybet\Cms
 */
class Error extends BaseErlybetModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'cms.errors';

    /**
     * @param string $lang
     * @param string $type
     * @param $codeError
     * @param $partnerId
     * @return mixed
     */
    public function getError($lang, $type, $codeError, $partnerId)
    {
        return static::where('lang', $lang)
            ->where('type', $type)
            ->where('code_error', $codeError)
            ->where('partner_id', $partnerId)
            ->limit(1)
            ->first();
    }
}
