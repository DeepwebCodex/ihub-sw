<?php

namespace App\Components\Formatters;

use Illuminate\Support\Facades\Response as ResponseFacade;

class BetGamesApiFormatter extends XmlApiFormatter
{
    public function formatResponse($statusCode, string $message, array $payload = [])
    {
        $payload = array_merge($message ? compact('message') : [], $this->getMetaData()?:[], $payload);

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'application/xml'
        ]);
    }
}