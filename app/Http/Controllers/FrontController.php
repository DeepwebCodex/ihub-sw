<?php

namespace App\Http\Controllers;

use App\Components\ExternalServices\Facades\RemoteSession;
use App\Components\Users\IntegrationUser;
use App\Facades\AppLog;
use App\Http\Controllers\Internal\CasinoController;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Liuggio\StatsdClient\Sender\SocketSender;
use Liuggio\StatsdClient\Service\StatsdService;
use Liuggio\StatsdClient\StatsdClient;

class FrontController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->authorizeUser($request);

        $baseUrl = url('/');

        $input = $request->all();

        $controller = app()->make(CasinoController::class);

        $gameTypes = array_get(json_decode(app()->call([$controller, 'allGameTypes'])->content(), true), 'gametypes', []);

        $gameProviders = array_get(json_decode(app()->call([$controller, 'allProviders'])->content(), true), 'providers', []);

        $games = array_get(json_decode(app()->call([$controller, 'allGames'], $request->all())->content(), true), 'games', []);

        return view('games', compact('gameTypes', 'gameProviders', 'input', 'games', 'baseUrl', 'user'));
    }

    private function authorizeUser(Request $request)
    {
        if($sid = $request->cookie('PHPSESSID')){
            $user_id = RemoteSession::start($sid)->get('user_id');
            if($user_id){
                return IntegrationUser::get($user_id, config('integrations.egt.service_id'), 'fake_frontend');
            }
        } else {
            if($request->isMethod(Request::METHOD_POST) && $request->input('login_name') && $request->input('login_password')){
                $response = app('Guzzle')::request(
                    'GET',
                    "http://favbet.dev",
                    [
                        RequestOptions::HEADERS => [
                            'User-Agent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36",
                            'Host' => 'favbet.dev',
                            'Origin' => 'http://favbet.dev',
                            'Referer' => 'http://favbet.dev',
                            'X-Requested-With' => 'XMLHttpRequest'
                        ],
                        RequestOptions::COOKIES => new CookieJar(true)
                    ]
                );

                $cookies = [];
                foreach($response->getHeader('Set-Cookie') as $item) {
                    preg_match('/(.*?)=(.*?)($|;|,(?! ))/', $item, $matches);
                    $cookies[$matches[1]] = $matches[2];
                }

                $sid = $cookies['PHPSESSID'];

                setcookie("PHPSESSID", $sid, time() + (60 * 20));

                $response = app('Guzzle')::request(
                    'POST',
                    "http://favbet.dev/accounting/api/login",
                    [
                        RequestOptions::HEADERS => [
                            'User-Agent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36",
                            'Host' => 'favbet.dev',
                            'Origin' => 'http://favbet.dev',
                            'Referer' => 'http://favbet.dev',
                            'X-Requested-With' => 'XMLHttpRequest',
                            'Cookie' => 'PHPSESSID='.$sid
                        ],
                        RequestOptions::COOKIES => new CookieJar(true),
                        RequestOptions::FORM_PARAMS => [
                            'username' => $request->input('login_name'),
                            'password' => $request->input('login_password')
                        ]
                    ]
                );

                if(array_get(json_decode($response->getBody()->getContents(), true), 'error_code') == ''){
                    $user_id = RemoteSession::start($sid)->get('user_id');
                    if($user_id){
                        return IntegrationUser::get($user_id, config('integrations.egt.service_id'), 'fake_frontend');
                    }
                }
            }
        }

        return null;
    }

    public function logOut(Request $request){
        if($sid = $request->cookie('PHPSESSID')){
            unset($_COOKIE['PHPSESSID']);
            setcookie('PHPSESSID', null, -1, '/');
        }

        return back()->withInput();
    }

    public function giveMoney(Request $request)
    {
        if($request->isMethod(Request::METHOD_POST)) {
            $amount = (int)$request->input('amount');
            $userId = (int)$request->input('userID');

            if($amount > 0 && $userId)
            {
                /** @var Builder $queryBuilder */

                $queryBuilder = \DB::connection('account')->table('user_payment_accounts');
                $result = $queryBuilder->where(['user_id' => $userId, 'is_active' => true])->update(['balance' => $amount]);

                if($result >= 1)
                {
                    $response = app('Guzzle')::request(
                        'GET',
                        "http://".config('external.api.account_op.host').':'.config('external.api.account_op.port').'/account/changed',
                        [
                            RequestOptions::QUERY => [
                                'id' => $userId
                            ]
                        ]
                    );

                }
            }
        }

        return back()->withInput();
    }
}
