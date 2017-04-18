<?php

namespace App\Components\Formatters;

use App\Components\ThirdParty\Array2Xml;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

/**
 * Class WirexGamingApiFormatter
 * @package App\Components\Formatters
 */
class WirexGamingApiFormatter extends XmlApiFormatter
{
    /**
     * @param array $data
     * @return string
     */
    public function format(array $data)
    {
        if (empty($data)) {
            return '';
        }
        if (!empty($this->getMetaField('method'))) {
            $data = [
                'ns2:' . $this->getMetaField('method') . 'Response' => [
                    '@attributes' => [
                        'xmlns:ns2' => 'http://ws.platform.commersite.com/'
                    ],
                    'return' => $data
                ],
            ];
        }
        return Array2Xml::createXML(
            'S:Envelope',
            [
                '@attributes' => [
                    'xmlns:S' => 'http://schemas.xmlsoap.org/soap/envelope/'
                ],
                'S:Body' => $data
            ]
        )->saveXML();
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    public function formatException(\Exception $exception)
    {
        list($payload, $statusCode) = array_values($this->transformException($exception));

        ksort($payload);

        return ResponseFacade::make($this->format($payload), 200, [
            'Content-type' => 'application/xml'
        ]);
    }

    /**
     * @param $statusCode
     * @param string $message
     * @param array $payload
     * @return mixed
     */
    public function formatResponse($statusCode, string $message, array $payload = [])
    {
        $payloadDefault = [
            'code' => 0,
            'message' => $message ?: 'Success',
            'status' => 'OK',
        ];
        $payload = array_merge($payloadDefault, $payload);

        ksort($payload);

        return ResponseFacade::make($this->format($payload), $statusCode, [
            'Content-type' => 'application/xml'
        ]);
    }
}
