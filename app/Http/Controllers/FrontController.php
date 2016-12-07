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
    public function index()
    {
        return view('welcome');
    }
}
