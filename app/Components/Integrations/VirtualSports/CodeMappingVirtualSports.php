<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/13/16
 * Time: 11:10 AM
 */

namespace App\Components\Integrations\VirtualSports;


use iHubGrid\ErrorHandler\Http\CodeMappingBase;
use ReflectionClass;

class CodeMappingVirtualSports extends CodeMappingBase
{
    const METHOD_NOT_FOUND = 'Method not found';
    const MISS_ELEMENT = 'Miss element';
    const CREATE_PARTICIPANT = 'Fail creat paricipant';
    const CREATE_EVENT_PARTICIPANT = 'Can\'t find id participant';
    const FAILED_INIT_RESULT = 'Scope data or participant or result type is bad';
    const CANT_CREATE_MARKET = 'Cant insert market';
    const CANT_CREATE_OUTCOME = 'Cant insert outcome';
    const CANT_FIND_MARKET = 'Can\'t find market';
    const CANT_CREATE_LINK = 'Cant insert link';
    const CANT_UPDATE_MARKET = 'Can\'t update market';
    const CANT_UPDATE_EVENT_STATUS = 'Can\'t update event status';
    const CANT_FIND_EVENT = 'Cant find event';
    const CANT_CREATE_RESULT = 'Can\'t insert result';
    const CANT_CREATE_GAME_TOTAL = 'Cant insert result game total';
    const CANT_CALCULATE_BET = 'Cant calculate bet';
    const EVENT_NOT_FOUND = 'Haven\'t event';
    const BAD_STATUS_PROGRESS = 'Bad status progress';
    const DONE_DUPLICATE = 'Done this is duplicate';
    const CANT_FIND_OUTCOME = 'Cant find outcome';
    const CANT_FIND_PARTICIPANT = 'Cant find 2participant';
    const CANT_VOID_FINISHED = 'cant_void_finished';
    const EVENT_MODEL_NOT_FOUND = 'cant_find_event';
    const WRONG_SPORT_ID_FOR_EVENT = 'wrong_sport_id_for_event';

    public static function getMapping()
    {
        $oClass = new ReflectionClass(__CLASS__);
        $mappings = $oClass->getConstants();

        $result = [];

        foreach ($mappings as $message) {
            if($message === 'server_error') {
                break;
            }

            $result[$message] = [
                'message'   => $message,
                'map'       => [],
                'attribute' => null,
                'meanings'  => [$message]
            ];
        }

        return $result;
    }
}