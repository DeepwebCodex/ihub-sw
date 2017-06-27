<?php
namespace App\Components\Integrations\MicroGaming\Orion\Request;

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

class GetFailedEndGameQueue extends Request
{

    const REQUEST_NAME = "GetFailedEndGameQueue";

    public function prepare(array $data = [])
    {
        $this->uuid = Uuid::uuid1()->toString();
        $dataTmp = [
            '@attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:adm' => 'http://mgsops.net/AdminAPI_Admin',
                'xmlns:arr' => 'http://schemas.microsoft.com/2003/10/Serialization/Arrays'
            ],
            'soapenv:Header' => '',
            'soapenv:Body' => [
                'adm:GetFailedEndGameQueue' => [
                    'adm:serverIds' => [
                        'arr:int' => Config::get('integrations.microgamingOrion.serverId')
                    ]
                ]
            ]
        ];

        $this->body = $this->source->create('soapenv:Envelope', $dataTmp);
    }
}
