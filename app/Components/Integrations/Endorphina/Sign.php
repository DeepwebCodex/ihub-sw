<?php
namespace App\Components\Integrations\Endorphina;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use function app;

/**
 * Description of Sign
 *
 * @author petroff
 */
class Sign
{

    static public function generate(array $data): string
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        try {
            $partnerId = app('GameSession')->get('partner_id');
        } catch (Exception $ex) {
            $partnerId = Request::route('partnerIdRouter');
        }

        $salt = Config::get("integrations.endorphina.partners_config.{$partnerId}.salt");

        ksort($data);
        $str = implode('', $data);
        $hash = sha1($str . $salt);
        return strtoupper($hash);
    }
}
