<?php

namespace App\Http\Middleware\NetEntertainment;

use App\Components\Integrations\NetEntertainment\ApiMethod;

/**
 * @package App\Http\Middleware
 */
class ParsePlayerIdOnOffline
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     */
    public function handle($request, \Closure $next)
    {
        $apiMethod = new ApiMethod($request->input('type'));
        if ($apiMethod->isOffline()) {
            if ($request->input('userid')) {
                $playerDataRaw = explode('_', $request->input('userid'));

                $playerData = [
                    'userId' => (int)array_get($playerDataRaw, 0, null),
                ];

                $request->merge($playerData);
            }
        }

        return $next($request);
    }

}
