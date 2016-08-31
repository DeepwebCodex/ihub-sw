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
use SoapBox\Formatter\Formatter;

class XmlApiFormatter extends BaseApiFormatter
{

    /**
     * @param array $data
     * @return string
     */
    public function format(array $data)
    {
        $formatter = Formatter::make($data, Formatter::ARR);

        return $formatter->toXml();
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
}