<?php

namespace App\Http\Middleware\NetEntertainment;

/**
 * @package App\Http\Middleware
 */
class ParsePlayerIdOnWin
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     */
    public function handle($request, \Closure $next)
    {
        if ($request->input('userid')) {
            $playerDataRaw = explode('_', $request->input('userid'));

            $playerData = [
                'userid' => (int)array_get($playerDataRaw, 0, null),
            ];

            $request->merge($playerData);
        }

        return $next($request);
    }

}
