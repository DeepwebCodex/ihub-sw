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

class ManuallyValidateBet extends Request {

    public function prepare(array $data = []) {

        $this->uuid = Uuid::uuid1()->toString();
        $this->method = "ManuallyValidateBet";
        $dataValidateBet = array();
        foreach ($data as $key => $value) {
            $dataValidateBet['ori:ValidteBetRequest'] [] = [
                'ori:ExternalReference' => $value['operationId'],
                'ori:RowId' => $value['a:RowId'],
                'ori:ServerId' => Config::get('integrations.microgamingOrion.serverId'),
                'ori:UnlockType' => $value['unlockType'],
                'ori:UserId' => $value['a:UserId']
            ];
        }

        $dataTmp = [
            '@attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:adm' => 'http://mgsops.net/AdminAPI_Admin',
                'xmlns:ori' => 'http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures'
            ],
            'soapenv:Header' => '',
            'soapenv:Body' => [
                'adm:ManuallyValidateBet' => [
                    'adm:validateRequests' => $dataValidateBet
                ]
            ]
        ];

        $this->body = $this->source->create('soapenv:Envelope', $dataTmp);
    }

}
