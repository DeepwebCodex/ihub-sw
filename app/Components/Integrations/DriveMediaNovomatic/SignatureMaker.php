<?php

namespace App\Components\Integrations\DriveMediaNovomatic;

use App\Exceptions\Api\ApiHttpException;

/**
 * Class SignatureMaker
 * @package App\Components\Integrations\Novomatic
 */
class SignatureMaker
{
    /**
     * @param string $space
     * @param array $requestData
     * @return string
     */
    public function make(string $space, array $requestData): string
    {
        return strtoupper(md5(NovomaticHelper::getKey($space) . http_build_query($requestData)));
    }

}
