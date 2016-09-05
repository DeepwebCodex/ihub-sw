<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:47 PM
 */

namespace App\Exceptions\Api\Templates;


class CasinoTemplate implements IExceptionTemplate
{
    private $strict = true;

    private $item;

    public function mapping($item)
    {
        $this->item = $item;

        $view = [
            'status' => false,
            'code' => (int)$this->useElement('code', 0),
            'message' => $this->useElement('message', 'Unknown'),
            'token' => $this->useElement('token', ''),
            'signature' => $this->useElement('signature', ''),
            'time' => time()
        ];

        if(!$this->strict){
            $view = array_merge($view, $this->item);
        }

        return $view;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}