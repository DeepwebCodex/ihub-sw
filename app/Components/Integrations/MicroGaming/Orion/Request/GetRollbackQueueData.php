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

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

class GetRollbackQueueData extends Request {

    const MODULE = "GetRollbackQueueData";

    public function prepare(array $data = []) {
        $this->uuid = Uuid::uuid1()->toString();
        $this->method = GetRollbackQueueData::MODULE;
        $dataTmp = [
            '@attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:adm' => 'http://mgsops.net/AdminAPI_Admin',
                'xmlns:arr' => 'http://schemas.microsoft.com/2003/10/Serialization/Arrays'
            ],
            'soapenv:Header' => '',
            'soapenv:Body' => [
                'adm:GetRollbackQueueData' => [
                    'adm:serverIds' => [
                        'arr:int' => Config::get('integrations.microgamingOrion.username')
                    ]
                ]
            ]
        ];

        $this->body = $this->source->create('soapenv:Envelope', $dataTmp);
    }

    public function parse(string $result): array {
        $resultArray = $this->parseSource($result);
        return $resultArray;
    }

}
