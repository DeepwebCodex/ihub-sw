<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 10:38 AM
 */

namespace App\Components\Formatters;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use SoapBox\Formatter\Formatter;

class JsonApiFormatter extends BaseApiFormatter
{
    /**
     * @param array $data
     * @return string
     */
    public function format(array $data){
        if($data) {
            $formatter = Formatter::make($data, Formatter::ARR);
            return $formatter->toJson();
        }

        return '';
    }


    /**
     * @param \Exception $exception
     * @return Response
     */
    public function formatException(\Exception $exception){

        list($payload, $statusCode) = array_values($this->transformException($exception));

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'application/json'
        ]);
    }

    public function formatResponse($statusCode, string $message, array $payload = []){

        $payload = array_merge($payload, $message ? compact('message') : [], $this->getMetaData()?:[]);

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'application/json'
        ]);
    }
}