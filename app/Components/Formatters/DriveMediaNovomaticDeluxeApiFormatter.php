<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 10:38 AM
 */

namespace App\Components\Formatters;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class DriveMediaNovomaticDeluxeApiFormatter extends JsonApiFormatter
{



    /**
     * @param Exception $exception
     * @return Response
     */
    public function formatException(Exception $exception)
    {

        list($payload, $statusCode) = array_values($this->transformException($exception));

        return ResponseFacade::make($this->format($payload), 200, [
            'Content-type' => 'application/json'
        ]);
    }


}