<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Requests\Validation\Orion;

/**
 * Description of CommitValidation
 *
 * @author petroff
 */
class EndGameValidation extends Validation {

    function __construct() {
        $this->rules = [];
        $this->rulesStructures = [
            's:Body' => 'required',
            's:Body.GetFailedEndGameQueueResponse' => 'required',
            's:Body.GetFailedEndGameQueueResponse.GetFailedEndGameQueueResult' => 'checkEmpty',
        ];
        $this->rulesElements = [
            'a:ClientId' => 'required',
            'a:Description' => 'present',
            'a:ModuleId' => 'required',
            'a:RowId' => 'required',
            'a:ServerId' => 'required',
            'a:SessionId' => 'required',
            'a:TimeCompleted' => 'present',
            'a:TimeCreated' => 'required',
            'a:TournamentId' => 'required',
            'a:TransNumber' => 'required',
            'a:UniqueId' => 'required',
            'a:UserId' => 'required',
        ];
        $this->nameElement = 'a:GetFailedGamesResponse';
    }

    public function getElements(array $data): array {
        return $data['s:Body']['GetFailedEndGameQueueResponse']['GetFailedEndGameQueueResult'][$this->nameElement];
    }

}
