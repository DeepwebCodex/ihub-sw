<?php

namespace App\Http\Controllers\Api\Base;

use App\Components\Formatters\BaseApiFormatter;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class BaseApiController extends Controller
{
    protected $protocolFormatter;

    public static $exceptionTemplate;

    protected $options;

    public function __construct(BaseApiFormatter $formatter)
    {
        $this->protocolFormatter = $formatter;
        $this->protocolFormatter->setTemplate(self::$exceptionTemplate);
    }


    public function respond($statusCode, string $message, array $payload = []){
        return $this->protocolFormatter->formatResponse($statusCode, $message, $payload);
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = "", array $payload = []){
        return $this->respond($statusCode, $message, $payload);
    }

    public function getOption(string $name, $default = null){
        return array_get($this->options, $name, $default);
    }

}
