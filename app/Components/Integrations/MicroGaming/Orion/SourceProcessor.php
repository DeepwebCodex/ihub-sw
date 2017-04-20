<?php

namespace App\Components\Integrations\MicroGaming\Orion;

use iHubGrid\ErrorHandler\ThirdParty\Array2Xml;
use Nathanmac\Utilities\Parser\Facades\Parser;

class SourceProcessor
{
    public function parser(string $xml): array
    {
        return Parser::xml($xml);
    }

    public function create(string $root, array $data): string
    {
        return Array2Xml::createXML($root, $data)->saveXML();
    }

}
