<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\DriveMediaNovomatic\CodeMapping;
use App\Components\Integrations\DriveMediaNovomatic\StatusCode;
use iHubGrid\ErrorHandler\Exceptions\Api\Templates\IExceptionTemplate;

/**
 * Class NovomaticTemplate
 * @package App\Exceptions\Api\Templates
 */
class DriveMediaNovomaticTemplate implements IExceptionTemplate
{
    private $item;

    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return array
     */
    public function mapping($item, $statusCode, $isApiException)
    {
        $this->item = $item;
        $code = (int)$this->useElement('code', StatusCode::INTERNAL_SERVER_ERROR);
        $message = $this->useElement('message', 'Unknown');
        $codeMap = CodeMapping::getByErrorCode($code);

        if ($codeMap) {
            $message = $codeMap['message'];
        }
        return [
            'status' => 'fail',
            'error' => $message
        ];
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    private function useElement($key, $default)
    {
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);
        return $val;
    }
}
