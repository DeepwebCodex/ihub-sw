<?php

namespace App\Components\Formatters;

use iHubGrid\ErrorHandler\Formatters\JsonApiFormatter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class NetEntertainmentApiFormatter extends JsonApiFormatter
{
    /**
     * @param \Exception $exception
     * @return Response
     */
    public function formatException(\Exception $exception)
    {

        list($payload, $statusCode) = array_values($this->transformException($exception));

        $codes = [
            Response::HTTP_REQUEST_TIMEOUT,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            Response::HTTP_SERVICE_UNAVAILABLE
        ];
        $httpCode = (in_array($statusCode, $codes)) ? Response::HTTP_REQUEST_TIMEOUT : Response::HTTP_OK;

        return ResponseFacade::make($this->format($payload), $httpCode, [
            'Content-type' => 'application/json'
        ]);
    }
}