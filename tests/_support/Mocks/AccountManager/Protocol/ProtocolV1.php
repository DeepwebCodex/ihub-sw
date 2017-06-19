<?php

namespace Testing\AccountManager\Protocol;

use Testing\AccountManager\Protocol\ProtocolInterface;
use Testing\AccountManager\Protocol\v1\ResponseGetFreeCardId;
use Testing\AccountManager\Protocol\v1\ResponseGetFreeOperationId;
use Testing\AccountManager\Protocol\v1\ResponseGetUserInfo;
use Testing\AccountManager\Protocol\v1\ResponseInterface;
use Testing\AccountManager\Protocol\v1\Transaction\ResponseCommitTransaction;
use Testing\AccountManager\Protocol\v1\Transaction\ResponseCreateTransaction;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @deprecated
 * Description of ProtocolV1
 *
 * @author petroff
 */
class ProtocolV1 implements ProtocolInterface
{

    private $path = 'Testing\\AccountManager\\Protocol\\v1\\';
    private $responseCreateTransaction;
    private $responseCommitTransaction;
    private $responseGetUserInfo;
    private $responseGetFreeOperationId;
    private $responseGetFreeCardId;
    

    function __construct()
    {
        $this->responseCreateTransaction = new ResponseCreateTransaction();
        $this->responseCommitTransaction = new ResponseCommitTransaction();
        $this->responseGetUserInfo = new ResponseGetUserInfo();
        $this->responseGetFreeOperationId = new ResponseGetFreeOperationId();
        $this->responseGetFreeCardId = new ResponseGetFreeCardId();
    }

    public function getResponse(string $name): ResponseInterface
    {
        //TODO make autoload class
        $className = 'response' . ucfirst($name);
        return $this->{$className};
    }

}
