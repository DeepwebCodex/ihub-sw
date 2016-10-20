<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:47 PM
 */

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\MicroGaming\CodeMapping;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Stringy\StaticStringy as S;

class VirtualBoxingTemplate implements IExceptionTemplate
{
    private $item;

    public function mapping($item, $statusCode, $isApiException)
    {
        $this->item = $item;

        if(!$isApiException && isset($this->item['message'])){
            $this->item['message'] = 'Error';
            if(isset($this->item['code'])){
                unset($this->item['code']);
            }
        }

        return $this->item;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}