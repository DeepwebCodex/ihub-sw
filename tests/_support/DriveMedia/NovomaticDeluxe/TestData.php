<?php

namespace DriveMedia\NovomaticDeluxe;

use App\Components\Integrations\DriveMediaNovomaticDeluxe\BetInfo;
use App\Components\Integrations\DriveMediaNovomaticDeluxe\Sign;
use iHubGrid\Accounting\Users\IntegrationUser;
use DriveMedia\TestUser;
use function GuzzleHttp\json_encode;

class TestData {
//    novomatic deluxe
//===========
//Req:
//{"cmd":"getBalance","space":"398","login":"player-19062","sign":"566F188B8FE080A7D0E49EA1A0A4C35C"}
//
//Resp:
//{"status":"success","error":"","login":"player-19062","balance":"672.63"}
//
//Req:
//{"cmd":"writeBet","space":"398","login":"player-19062","bet":"0.2","winLose":"-0.2","tradeId":"1485345811243_1229381635","betInfo":"SpinNormal",
//"gameId":"223","matrix":"PPJPA;K9P1C;WC9WK;","WinLines":"Line_8:3xP=20;","date":1485345790,"sign":"5F3FEEC05009C015C67C844ED3EC0FB1"}
//
//Resp:
//{"operationId":"190621485345795","status":"success","error":"","login":"player-19062","balance":"672.43"}
//
//Req:
//{"cmd":"writeBet","space":"398","login":"player-19062","bet":"0.0","winLose":"0.2","tradeId":"1485345823892_null","betInfo":"CollectWin","gameId":"223",
//"matrix":0,"WinLines":0,"date":1485345803,"sign":"8C170054FB6E37AC3F1D5613197B9EF3"}
//
//Resp:
//{"operationId":"190621485345804","status":"success","error":"","login":"player-19062","balance":"672.63"} 

    /**
     * @var IntegrationUser
     */
    private $user;
    private $amount;
    private $gameId = 123;

    public function __construct(TestUser $testUser) {
        $this->user = $testUser->getUser();
        $this->amount = 10;
    }

    public function sign(array $data) {
        return Sign::generate($data);
    }

    private function getLogin() {
        return (string) $this->user->id . "--" . "1---5--127-0-0-1";
    }

    public function getData() {
        return time();
    }

    public function getDataMethodUnknown() {

        $data = [
            "cmd" => "unknow method",
            "space" => "1808",
            "login" => $this->getLogin(),
        ];
        $data['sign'] = $this->sign($data);
        return json_encode($data);
    }

    public function getDataGetBalance() {
        $data = [
            "cmd" => "getBalance",
            "space" => "1808",
            "login" => $this->getLogin()
        ];
        $data['sign'] = $this->sign($data);
        return json_encode($data);
    }

    public function getWrongPacket() {
        $data = [
            "cmd" => "getBalance",
            "space" => "1808",
                //"login" => $this->getLogin(),
        ];
        $data['sign'] = $this->sign($data);
        return json_encode($data);
    }

    public function getBetPacket() {
        $betAmount = 100.23;
        $winAmount = -100.23;
        $data = [
            "cmd" => "writeBet",
            "space" => "1808",
            "login" => $this->getLogin(),
            "bet" => $betAmount,
            "winLose" => $winAmount,
            "tradeId" => microtime(),
            "betInfo" => BetInfo::BET,
            "gameId" => $this->gameId,
            "matrix" => 'test matrix',
            "date" => $this->getData()
        ];
        $data['sign'] = $this->sign($data);
        return json_encode($data);
    }

    public function getWinPacket() {
        $betAmount = 100.23;
        $winAmount = 14;
        $data = [
            "cmd" => "writeBet",
            "space" => "1808",
            "login" => $this->getLogin(),
            "bet" => $betAmount,
            "winLose" => $winAmount,
            "tradeId" => microtime(),
            "betInfo" => BetInfo::GAMBLE,
            "gameId" => $this->gameId,
            "matrix" => 'test matrix',
            "date" => $this->getData()
        ];
        $data['sign'] = $this->sign($data);
        return json_encode($data);
    }

    public function getFreePacket($betAmount, $winAmount, $betInfo) {
        $data = [
            "cmd" => "writeBet",
            "space" => "1808",
            "login" => $this->getLogin(),
            "bet" => $betAmount,
            "winLose" => $winAmount,
            "tradeId" => microtime(),
            "betInfo" => $betInfo,
            "gameId" => $this->gameId,
            "matrix" => 'test matrix',
            "date" => $this->getData()
        ];
        $data['sign'] = $this->sign($data);
        return json_encode($data);
    }

    public function getWrongSign() {
        $data = [
            "cmd" => "getBalance",
            "space" => "1808",
            "login" => $this->getLogin(),
        ];
        $data['sign'] = $this->sign($data) . "WRONGSIGN";
        return json_encode($data);
    }

    public function getBalanceSignRaw() {
        $data = [
            "cmd" => "getBalance",
            "space" => "1808",
            "login" => $this->getLogin()
        ];
        $data['sign'] = $this->sign($data);
        return $data;
    }

    public function getFloatPacket() {
        $betAmount = "1.2";
        $winAmount = "0.4";
        $data = [
            "cmd" => "writeBet",
            "space" => "1808",
            "login" => $this->getLogin(),
            "bet" => $betAmount,
            "winLose" => $winAmount,
            "tradeId" => microtime(),
            "betInfo" => BetInfo::GAMBLE,
            "gameId" => $this->gameId,
            "matrix" => 'test matrix',
            "date" => $this->getData()
        ];
        $data['sign'] = $this->sign($data);
        return json_encode($data);
    }

}
