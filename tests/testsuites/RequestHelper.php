<?php

use PHPUnit\Framework\TestCase;
use AppUtils\RequestHelper;
use AppUtils\RequestHelper_Exception;

final class RequestHelperTest extends TestCase
{
    public function test_sendEmpty()
    {
        $helper = new RequestHelper('http://www.foo.nowhere');
        
        $this->expectException(RequestHelper_Exception::class);
        
        $helper->send();
    }
}
