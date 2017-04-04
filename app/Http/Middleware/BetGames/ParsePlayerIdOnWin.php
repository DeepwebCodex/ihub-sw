<?php

namespace App\Http\Middleware\BetGames;

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
        if ($request->input('params.player_id') && $request->input('params.currency')) {
            $playerId = (int)str_ireplace(strtoupper($request->input('params.currency')), '', $request->input('params.player_id'));

            $playerData = [
                'params' => [
                    'player_id' => $playerId,
                ]
            ];

            $params = $request->all();

            $params = array_replace_recursive($params, $playerData);

            $request->merge($params);
        }

        return $next($request);
    }

}
