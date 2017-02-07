<?php

namespace App\Components\Formatters;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class NetEntApiFormatter extends JsonApiFormatter
{
    /**
     * @param \Exception $exception
     * @return Response
     */
    public function formatException(\Exception $exception)
    {

        list($payload, $statusCode) = array_values($this->transformException($exception));

        $httpCode = ($statusCode == Response::HTTP_REQUEST_TIMEOUT) ? Response::HTTP_REQUEST_TIMEOUT : Response::HTTP_OK;

        return ResponseFacade::make($this->format($payload), $httpCode, [
            'Content-type' => 'application/json'
        ]);
    }
}