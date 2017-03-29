<?php

namespace App\Components\Integrations\MicroGaming\Orion\Request;

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

class ManuallyCompleteGame extends Request
{

    public function prepare(array $data = [])
    {
        $this->uuid = Uuid::uuid1()->toString();
        $this->method = "ManuallyCompleteGame";
        $dataValidateComplete = array();
        foreach ($data as $key => $value) {
            $dataValidateComplete['ori:CompleteGameRequest'] [] = [
                'ori:RowIdLong' => ($value['a:RowId']) ?? $value['a:RowIdLong'],
                'ori:ServerId' => Config::get('integrations.microgamingOrion.serverId'),
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
                'adm:ManuallyCompleteGame' => [
                    'adm:requests' => $dataValidateComplete
                ]
            ]
        ];

        $this->body = $this->source->create('soapenv:Envelope', $dataTmp);
    }

}
