<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 10:38 AM
 */

namespace App\Components\Formatters;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use SimpleXMLElement;


class XmlApiFormatter extends BaseApiFormatter
{

    /**
     * @param array $data
     * @return string
     */
    public function format(array $data)
    {
        if($data) {
            return $this->arrayToXml($data);
        }

        return '';
    }

    private function arrayToXml(array $data, $xml = null){
        if (is_null($xml)) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root/>');
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->arrayToXml($value, $xml->addChild($key));
            } else {
                $xml->addChild($key, $value);
            }
        }

        return $xml->asXML();
    }


    /**
     * @param \Exception $exception
     * @return Response
     */
    public function formatException(\Exception $exception)
    {
        list($payload, $statusCode) = array_values($this->transformException($exception));

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'application/xml'
        ]);
    }

    public function formatResponse($statusCode, string $message, array $payload = []){

        $payload = array_merge($payload, $message ? compact('message') : [], $this->getMetaData()?:[]);

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'application/xml'
        ]);
    }
}