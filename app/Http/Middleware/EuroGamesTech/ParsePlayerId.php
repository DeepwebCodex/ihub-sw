<?php

namespace App\Http\Middleware\EuroGamesTech;

/**
 * @package App\Http\Middleware
 */
class ParsePlayerId
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     */
    public function handle($request, \Closure $next)
    {
        $playerDataRaw = explode('_', $request->input('PlayerId', ''));

        // parse format "playerId_partnerId_cashdeskId_userIP"
        $playerData = [
            'PlayerId'   => rtrim(array_get($playerDataRaw, 0, null), '_'), // Hack for big integer user id on staging env ('_')
            'PartnerId'  => array_get($playerDataRaw, 1, null),
            'CashdeskId' => array_get($playerDataRaw, 2, null),
            'UserIp'     => array_get($playerDataRaw, 3, null)
        ];

        $request->merge($playerData);

        return $next($request);
    }

}