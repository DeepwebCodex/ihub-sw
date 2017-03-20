<?php

namespace App\Http\Middleware\DriveMedia;

/**
 * @package App\Http\Middleware
 */
class ParseLogin {

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     */
    public function handle($request, \Closure $next) {
        $playerDataRaw = explode('--', $request->input('login', ''));

        // parse format "playerId--partnerId--cashdeskId--playerIp"
        $playerData = [
            'userId' => array_get($playerDataRaw, 0, null),
            'partnerId' => array_get($playerDataRaw, 1, null),
            'cashdeskId' => array_get($playerDataRaw, 2, null),
            'userIp' => array_get(str_replace("-", ".", $playerDataRaw), 3, null),
        ];

        $request->merge($playerData);

        return $next($request);
    }

}
