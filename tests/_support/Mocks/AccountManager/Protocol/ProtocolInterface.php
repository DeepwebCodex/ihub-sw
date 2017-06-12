<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Testing\AccountManager\Protocol;

use Testing\AccountManager\Protocol\v1\ResponseInterface;



/**
 * @deprecated
 *
 * @author petroff
 */
interface ProtocolInterface
{
    public function getResponse(string $name): ResponseInterface;
}
