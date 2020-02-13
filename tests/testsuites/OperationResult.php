<?php

use PHPUnit\Framework\TestCase;

use AppUtils\OperationResult;

final class OperationResultTest extends TestCase
{
    public function test_create_pristine()
    {
        $result = new OperationResult(new stdClass());
        
        $this->assertTrue($result->isValid());
        $this->assertFalse($result->hasCode());
        $this->assertSame(0, $result->getCode());
        $this->assertEmpty($result->getErrorMessage());
        $this->assertEmpty($result->getSuccessMessage());
    }

    public function test_makeSuccess()
    {
        $result = new OperationResult(new stdClass());
        
        $result->makeSuccess('Success');
        
        $this->assertTrue($result->isValid());
        $this->assertEquals('Success', $result->getSuccessMessage());
        $this->assertEmpty($result->getErrorMessage());
        $this->assertFalse($result->hasCode());
        $this->assertSame(0, $result->getCode());
    }

    public function test_makeSuccess_code()
    {
        $result = new OperationResult(new stdClass());
        
        $result->makeSuccess('Success', 55);
        
        $this->assertTrue($result->hasCode());
        $this->assertSame(55, $result->getCode());
    }

    public function test_makeError()
    {
        $result = new OperationResult(new stdClass());
        
        $result->makeError('Error', 45);
        
        $this->assertFalse($result->isValid());
        $this->assertEquals('Error', $result->getErrorMessage());
        $this->assertEmpty($result->getSuccessMessage());
        $this->assertTrue($result->hasCode());
        $this->assertSame(45, $result->getCode());
    }
    
    public function test_switchStatus()
    {
        $result = new OperationResult(new stdClass());
        
        $result->makeError('Error', 45);
        
        $result->makeSuccess('Success', 33);

        $this->assertTrue($result->isValid());
        $this->assertEquals('Success', $result->getSuccessMessage());
        $this->assertEmpty($result->getErrorMessage());
        $this->assertTrue($result->hasCode());
        $this->assertSame(33, $result->getCode());
    }
}
