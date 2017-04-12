<?php

namespace App\Components\Integrations\DriveMediaNovomatic;

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
        $secretKey = config("integrations.DriveMediaNovomatic.spaces.{$space}.key");

        return strtoupper(md5($secretKey . http_build_query($requestData)));
    }
}
