<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 10:38 AM
 */

namespace App\Components\Formatters;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Route;
use SimpleXMLElement;


class EgtXmlApiFormatter extends XmlApiFormatter
{

    /**
     * @param array $data
     * @return string
     */
    public function format(array $data)
    {
        if($data) {

            $currentAction = Route::currentRouteAction();

            $method = '';

            if($currentAction) {
                list($controller, $method) = explode('@', $currentAction);
            }

            return $this->arrayToXml($data, $this->mapMethod($method));
        }

        return '';
    }

    private function arrayToXml(array $data, string $root, $xml = null){
        if (is_null($xml)) {
            $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><{$root}/>");
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->arrayToXml($value, $xml->addChild($key));
            } else {
                $xml->addChild($key, $value);
            }
        }

        return $xml->asXML();
    }

    private function mapMethod(string $method){
        $map = [
            'authenticate'          => 'Auth',
            'getPlayerBalance'      => 'GetPlayerBalance',
            'withdraw'              => 'Withdraw',
            'deposit'               => 'Deposit',
            'withdrawAndDeposit'    => 'WithdrawAndDeposit'
        ];

        return array_get($map, $method, 'Error') . 'Response';
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
}