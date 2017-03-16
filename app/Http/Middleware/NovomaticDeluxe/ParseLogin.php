<?php

namespace App\Http\Middleware\NovomaticDeluxe;

use Closure;
use Illuminate\Http\Request;
use function array_get;

/**
 * @package App\Http\Middleware
 */
class ParseLogin {

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     *
     */
    public function handle($request, Closure $next) {
        $playerDataRaw = explode('_', $request->input('login', ''));

        // parse format "playerId_partnerId_cashdeskId"
        $playerData = [
            'userId' => array_get($playerDataRaw, 0, null),
            'currency' => array_get($playerDataRaw, 1, null),
            'partnerId' => array_get($playerDataRaw, 2, null),
            'cashdeskId' => array_get($playerDataRaw, 3, null),
        ];

        $request->merge($playerData);

        return $next($request);
    }

}
