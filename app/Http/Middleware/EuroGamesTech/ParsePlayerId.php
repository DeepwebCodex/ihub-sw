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

        // parse format "playerId_partnerId_cashdeskId"
        $playerData = [
            'PlayerId' => $playerDataRaw[0] ?? '',
            'PartnerId' => $playerDataRaw[1] ?? '',
            'CashdeskId' => $playerDataRaw[2] ?? '',
        ];

        $request->merge($playerData);

        return $next($request);
    }

}
