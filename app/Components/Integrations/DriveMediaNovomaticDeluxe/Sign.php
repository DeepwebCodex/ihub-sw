<?php


namespace App\Components\Integrations\DriveMediaNovomaticDeluxe;

use Illuminate\Support\Facades\Config;

/**
 * Description of Sign
 *
 * @author petroff
 */
class Sign
{

    /**
     * @param array $data
     * @return string
     */
    static public function generate(array $data): string
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }

        $spaces = Config::get("integrations.DriveMediaNovomaticDeluxe.spaces");
        $currentSpace = $data['space'] ?? '';
        foreach ($spaces as $v) {
            if ($v['id'] === $currentSpace) {
                $secretKey = $v['key'];
                break;
            }
        }
        $str = http_build_query($data);
        $hash = md5($secretKey . $str);

        return strtoupper($hash);
    }

}
