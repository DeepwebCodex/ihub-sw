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

class MicroGamingApiFormatter extends XmlApiFormatter
{

    /**
     * @param array $data
     * @return string
     */
    public function format(array $data)
    {
        if($data) {
            return Array2Xml::createXML('pkt', $data)->saveXML();
        }

        return '';
    }
}