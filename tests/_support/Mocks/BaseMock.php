<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Testing;

use Mockery;

/**
 * Description of BaseMock
 *
 * @author petroff
 */
class BaseMock
{

    protected $I;
    protected $class;
    protected $disable = false;

    function __construct($I = '')
    {
        $this->I = $I;
    }

    function setDisable($disable)
    {
        $this->disable = $disable;
    }

    protected function getMocker($entity, string $method, $response, string $type = '', array $constructor = [])
    {
        $entityMock = $this->createMock($entity, $method, $response, $type, $constructor);
        return $this->injectMock($entityMock);
    }

    protected function injectMock($entityMock)
    {
        if ($this->I && !$this->disable) {
            $this->I->getApplication()->instance($this->class, $entityMock);
            $this->I->haveInstance($this->class, $entityMock);
        }
        return $entityMock;
    }

    protected function createMock($entity, string $method, $response, string $type = '', array $constructor = [])
    {
        if (is_object($entity)) {
            $entityMock = $entity;
        } else {
            if ($constructor) {
                $entityMock = Mockery::mock($entity, $constructor);
            } else {
                $entityMock = Mockery::mock($entity);
            }
            $this->class = $entity;
            $entityMock->makePartial()->shouldAllowMockingProtectedMethods();
        }

        if ($type) {
            $entityMock->shouldReceive($method)->andSet($type, $response);
        } else {
            $entityMock->shouldReceive($method)->andReturn($response);
        }

        return $entityMock;
    }

}
