<?php

namespace App\Http\Controllers\Api\Base;

use App\Components\Formatters\JsonApiFormatter;
use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    protected $protocolFormatter;

    public static $exceptionTemplate;


    public function __construct(JsonApiFormatter $formatter)
    {
        $this->protocolFormatter = $formatter;
        $this->protocolFormatter->setTemplate(self::$exceptionTemplate);
    }
}
