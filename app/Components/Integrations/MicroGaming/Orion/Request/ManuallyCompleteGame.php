<?php
namespace App\Components\Integrations\MicroGaming\Orion\Request;

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

class ManuallyCompleteGame extends Request
{

    const REQUEST_NAME = "ManuallyCompleteGame";

    public function prepare(array $data = [])
    {
        $this->uuid = Uuid::uuid1()->toString();
        $dataValidateComplete = array();
        foreach ($data as $value) {
            $dataValidateComplete['ori:CompleteGameRequest'] [] = [
                $value['PreparedRowId']['name'] => $value['PreparedRowId']['value'],
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
