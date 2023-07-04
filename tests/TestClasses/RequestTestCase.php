<?php

declare(strict_types=1);

namespace AppUtilsTests\TestClasses;

use TestClasses\BaseTestCase;

abstract class RequestTestCase extends BaseTestCase
{
    protected function setUp() : void
    {
        $_REQUEST = array();
    }

    protected function tearDown() : void
    {
        $_REQUEST = array();
    }

    protected function setUniqueParam($value) : string
    {
        $name = $this->generateUniqueParamName();
        $_REQUEST[$name] = $value;

        return $name;
    }

    protected int $paramCounter = 0;

    protected function generateUniqueParamName() : string
    {
        $this->paramCounter++;

        return 'foo'.$this->paramCounter;
    }
}
