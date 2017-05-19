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
class ResponseGetFreeOperationId implements ResponseInterface
{

    public function getBase(array $params)
    {
        if ($params['free_operation_id']) {
            return $params['free_operation_id'];
        }
        $default = DefaultParams::get();
        return $default->getUniqueId();
    }

    public function getProtocol(array $params)
    {
        $params = $this->validation($params);
        $base = $this->getBase($params);
        return $base;
    }

    public function validation(array $params): array
    {
        if (!isset($params['free_operation_id'])) {
            throw new Exception('free_operation_id must be fill');
        }

        return $params;
    }

}
