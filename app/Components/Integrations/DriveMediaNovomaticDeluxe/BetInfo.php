<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\DriveMediaNovomaticDeluxe;

/**
 * Description of BetInfo
 *
 * @author petroff
 */
class BetInfo {
    const BET = 'bet';
    const GAMBLE = 'Gamble';
    static public $betKeys = [self::BET, 'spin', 'SpinNormal'];
    static public $winKeys = ['CollectWin', 'CollectWinDisconnect'];
    
}
