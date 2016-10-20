<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index(Request $request)
    {
        $baseUrl = url('/');

        $input = $request->all();

        /*Games grand test*/

        $gameTypes = array_get(json_decode(file_get_contents($baseUrl.'/internal/games/allgametypes'), true), 'gametypes', []);

        $gameProviders = array_get(json_decode(file_get_contents($baseUrl.'/internal/games/allproviders'), true), 'providers', []);

        $games = array_get(json_decode(file_get_contents($baseUrl.'/internal/games/allgames?' . http_build_query($request->all())), true), 'games', []);

        return view('games', compact('gameTypes', 'gameProviders', 'input', 'games', 'baseUrl'));
    }
}
