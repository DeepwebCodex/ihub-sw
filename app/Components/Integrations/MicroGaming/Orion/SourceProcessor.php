<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\MicroGaming\Orion;

use App\Components\ThirdParty\Array2Xml;
use Nathanmac\Utilities\Parser\Facades\Parser;



/**
 * Description of XmlProcessor
 *
 * @author petroff
 */
class SourceProcessor {

    //Array2Xml::createXML('soapenv:Envelope', $dataTmp)->saveXML();
    //$result = Parser::xml($result);

    public function parser(string $xml): array {
        return Parser::xml($xml);
    }

    public function create(string $root, array $data): string {
        return Array2Xml::createXML($root, $data)->saveXML();
    }

}
