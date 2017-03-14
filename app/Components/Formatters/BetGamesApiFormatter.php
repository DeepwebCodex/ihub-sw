<?php

namespace App\Components\Formatters;

use Illuminate\Support\Facades\Response as ResponseFacade;

class BetGamesApiFormatter extends XmlApiFormatter
{
    /**
     * @param $statusCode
     * @param string $message
     * @param array $payload
     * @return mixed
     */
    public function formatResponse($statusCode, string $message, array $payload = [])
    {
        $payload = array_merge($message ? compact('message') : [], $this->getMetaData()?:[], $payload);

//        array_walk_recursive($payload, function ($item) {
//            $item = transliterate(str_slug($item, '_'));
//            return $item;
//        });

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'application/xml'
        ]);
    }

    /**
     * @param \Exception $exception
     * @return mixed
     */
    public function formatException(\Exception $exception)
    {
        list($payload, $statusCode) = array_values($this->transformException($exception));

        return ResponseFacade::make($this->format($payload), 200, [
            'Content-type' => 'application/xml'
        ]);
    }
}