<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\GameArt\StatusCode;

class GameArtTemplate implements IExceptionTemplate
{
    private $item;

    public function mapping($item, $statusCode, $isApiException)
    {
        $this->item = $item;

        $code = (int)$this->useElement('code', StatusCode::SERVER_ERROR);
        $message = $this->useElement('message', 'Unknown');

        if($code === 4) {
            $code = 403;
        } else {
            $code = 500;
        }

        $view = [
            'status' => (string)$code,
            'msg' => $message
        ];

        return $view;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}