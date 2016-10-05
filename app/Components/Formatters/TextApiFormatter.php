<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 10:38 AM
 */

namespace App\Components\Formatters;


use App\Components\ThirdParty\Array2Xml;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class TextApiFormatter extends BaseApiFormatter
{

    /**
     * @param array $data
     * @return string
     */
    public function format(array $data)
    {
        if($data) {
            return implode(' ', $data);
        }

        return '';
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    public function formatException(\Exception $exception)
    {
        list($payload, $statusCode) = array_values($this->transformException($exception));

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'text/plain'
        ]);
    }

    public function formatResponse($statusCode, string $message, array $payload = [])
    {

        $payload = array_merge($this->getMetaData()?:[], $payload, $message ? compact('message') : []);

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'text/plain'
        ]);
    }
}