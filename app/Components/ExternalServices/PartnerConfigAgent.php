<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/2/16
 * Time: 10:25 AM
 */

namespace App\Components\ExternalServices;


use App\Components\ExternalServices\Traits\RohRequest;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * @param Request $request;
*/
class PartnerConfigAgent
{
    use RohRequest;
    /*partner config keys*/
    const P_KEY_CROATIA     = 'croatia_monitoring';
    const P_KEY_TAX_RATE    = 'tax_rate';
    const P_KEY_TAX_TYPE    = 'tax_type';
    const P_KEY_TAX_RATE2   = 'tax_rate2';
    const P_KEY_TAX_TYPE2   = 'tax_type2';
    const P_KEY_TAX_RATE3   = 'tax_rate3';
    const P_KEY_TAX_TYPE3   = 'tax_type3';
    const P_KEY_BONUS_TYPE  = 'bonus_type';
    const P_KEY_BONUS_RATE  = 'bonus_rate';

    /*CashDesc config keys*/
    const C_KEY_CURRENCY = 'currency';
    const C_KEY_CITY = 'city';
    const C_KEY_ADDRESS = 'address';
    const C_KEY_PARTNER_IIN = 'partner_iin';
    const C_KEY_PARTNER_LEGAL_ADDRESS = 'partner_legal_address';
    const C_KEY_PARTNER_NAME = 'partner_name';


    //api partner config
    private $configRohHost;
    private $configRohPort;

    private $request;

    public function __construct()
    {

        $this->setUpConfig();


        $this->request = app('Request')::getFacadeRoot();
    }

    private function setUpConfig(){

        if(!config('external.api.config_agent_roh')) {
            throw new InvalidResourceException("Invalid API configuration");
        }

        $this->configRohHost = config('external.api.config_agent_roh.host');
        $this->configRohPort = config('external.api.config_agent_roh.port');
    }

    /**
     * Gets config for given partner by provided array of keys
     *
     * @param int $partnerId
     * @param array $keys
     * @return mixed
     */
    public function getPartnerConfig(int $partnerId, array $keys = []){
        return $this->postMessageRoh('confagent/get_partner_configs',[
            'partner_id' => $partnerId,
            'keys'  => $keys
        ]);
    }

    /**
     * Gets sccs config for croatia monitoring
     *
     * @param int $partnerId
     * @return mixed
     */
    public function getSccsConfig(int $partnerId){
        return $this->postMessageRoh('confagent/get_partner_config',[
            'partner_id' => $partnerId,
            'key'  => self::P_KEY_CROATIA
        ]);
    }

    /**
     * Get config values for cashDesk by given keys
     *
     * @param int $cashDescId
     * @param array $keys
     * @return mixed
     */
    public function getCashDescInfo(int $cashDescId, array $keys = []){
        return $this->postMessageRoh('confagent/get_cashdesk_config_values', [
            'cashdesk_id'   => $cashDescId,
            'keys'          => $keys
        ]);
    }

    private function postMessageRoh(string $method, array $params, int $retry = 0)
    {
        return $this->sendPostRoh($this->buildCardsRohHost($method), $params, $retry);
    }

    private function buildCardsRohHost(string $method)
    {
        return 'http://' . $this->configRohHost . ':' . $this->configRohPort . '/' . $method;
    }
}