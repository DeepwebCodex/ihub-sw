<?php

namespace App\Components\Formatters;

use Exception;
use iHubGrid\ErrorHandler\Formatters\JsonApiFormatter;
use Illuminate\Support\Facades\Response as ResponseFacade;


class EndorphinaApiFormatter extends JsonApiFormatter
{

    /**
     * @param Exception $exception
     * @return Response
     */
    public function formatException(Exception $exception)
    {

        list($payload, $statusCode) = array_values($this->transformException($exception));

        if (in_array($statusCode, [401, 402, 403, 404, 500])) {
            $httpCode = $statusCode;
        } else {
            $httpCode = 500;
        }


        return ResponseFacade::make($this->format($payload), $httpCode, [
                    'Content-type' => 'application/json'
        ]);
    }

}
