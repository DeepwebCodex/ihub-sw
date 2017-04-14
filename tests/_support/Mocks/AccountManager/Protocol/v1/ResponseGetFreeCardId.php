<?php

namespace Testing\AccountManager\Protocol\v1;

use Exception;
use Testing\AccountManager\Protocol\v1\DefaultParams;
use Testing\AccountManager\Protocol\v1\ResponseInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResponseUserInfo
 *
 * @author petroff
 */
class ResponseGetFreeCardId implements ResponseInterface
{

    public function getBase()
    {
        $default = DefaultParams::get();
        return $default->getUniqueId();
    }

    public function getProtocol(array $params)
    {
        $params = $this->validation($params);
        $base = $this->getBase();
        return $base;
    }

    public function validation(array $params): array
    {
        if (!isset($params['free_card_id'])) {
            throw new Exception('free_card_id must be numeric');
        }

        return $params;
    }

}
