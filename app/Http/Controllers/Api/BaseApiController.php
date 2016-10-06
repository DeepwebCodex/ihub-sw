<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\BaseApiFormatter;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseApiController
 * @package App\Http\Controllers\Api
 */
class BaseApiController extends Controller
{
    protected $protocolFormatter;

    public static $exceptionTemplate;

    protected $options;

    /**
     * BaseApiController constructor.
     * @param BaseApiFormatter $formatter
     */
    public function __construct(BaseApiFormatter $formatter)
    {
        $this->protocolFormatter = $formatter;
        $this->protocolFormatter->setTemplate(self::$exceptionTemplate);
    }

    /**
     * @param $statusCode
     * @param string $message
     * @param array $payload
     * @return \Illuminate\Http\Response
     */
    public function respond($statusCode, string $message, array $payload = [])
    {
        return $this->protocolFormatter->formatResponse($statusCode, $message, $payload);
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @param array $payload
     * @return \Illuminate\Http\Response
     */
    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = [])
    {
        return $this->respond($statusCode, $message, $payload);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return array_get($this->options, $name, $default);
    }
}
