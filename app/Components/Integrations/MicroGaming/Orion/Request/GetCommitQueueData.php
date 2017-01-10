<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GetCommitQueueData
 *
 * @author petroff
 */

namespace App\Components\Integrations\MicroGaming\Orion\Request;

use App\Components\ThirdParty\Array2Xml;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

class GetCommitQueueData extends Request {

    const MODULE = "GetCommitQueueData";

    public function prepare() {
        $this->uuid = Uuid::uuid1()->toString();
        $this->method = GetCommitQueueData::MODULE;
        $data = [
            '@attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:adm' => 'http://mgsops.net/AdminAPI_Admin',
                'xmlns:arr' => 'http://schemas.microsoft.com/2003/10/Serialization/Arrays'
            ],
            'soapenv:Header' => '',
            'soapenv:Body' => [
                'adm:GetCommitQueueData' => [
                    'adm:serverIds' => [
                        'arr:int' => Config::get('integrations.microgamingOrion.username')
                    ]
                ]
            ]
        ];

        $this->body = Array2Xml::createXML('soapenv:Envelope', $data)->saveXML();
    }

    public function parse(string $result): array {
        $result = $this->parseXml($result);
        return $result;
    }

}
