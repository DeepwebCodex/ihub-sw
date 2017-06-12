<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Testing\AccountManager\Protocol\v1;

/**
 * @deprecated
 * @author petroff
 */
interface ResponseInterface
{

    public function getProtocol(array $params);

    public function validation(array $params):array;
}
