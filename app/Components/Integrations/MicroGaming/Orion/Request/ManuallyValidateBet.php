<?php
namespace App\Components\Integrations\MicroGaming\Orion\Request;

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

class ManuallyValidateBet extends Request
{

    const REQUEST_NAME = "ManuallyValidateBet";

    public function prepare(array $data = [])
    {

        $this->uuid = Uuid::uuid1()->toString();
        $dataValidateBet = array();
        foreach ($data as $value) {
            $tmp = [
                'ori:ExternalReference' => $value['operationId'],
                $value['PreparedRowId']['name'] => $value['PreparedRowId']['value'],
                'ori:ServerId' => Config::get('integrations.microgamingOrion.serverId'),
                'ori:UnlockType' => $value['unlockType'],
                'ori:UserId' => $value['a:UserId']      
            ];
            $dataValidateBet['ori:ValidteBetRequest'] [] = $tmp;
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
