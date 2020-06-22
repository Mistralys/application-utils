<?php

use PHPUnit\Framework\TestCase;

use AppUtils\OperationResult;
use AppUtils\OperationResult_Collection;

final class OperationResultTest extends TestCase
{
    public function test_create_pristine()
    {
        $subject = new DateTime();
        
        $result = new OperationResult($subject);
        
        $this->assertTrue($result->isValid());
        $this->assertFalse($result->hasCode());
        $this->assertSame(0, $result->getCode());
        $this->assertEmpty($result->getErrorMessage());
        $this->assertEmpty($result->getNoticeMessage());
        $this->assertEmpty($result->getWarningMessage());
        $this->assertEmpty($result->getSuccessMessage());
        $this->assertInstanceOf(DateTime::class, $result->getSubject());
    }
    
    public function test_incrementID()
    {
        $result1 = new OperationResult(new stdClass());
        $result2 = new OperationResult(new stdClass());
        
        $this->assertTrue($result1->getID() !== $result2->getID());
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
    
    public function test_collection()
    {
        $subject = new stdClass();
        $subject->name = 'subject';
        
        $result = new OperationResult_Collection($subject);
        
        $result->makeError('Error 1', 45);
        $result->makeError('Error 2', 30);
        $result->makeSuccess('Success 1', 20);
        
        $this->assertFalse($result->isValid());
        $this->assertEquals('Error 1', $result->getErrorMessage());
        $this->assertEquals('Success 1', $result->getSuccessMessage());
        $this->assertTrue($result->hasCode());
        $this->assertSame(45, $result->getCode());
        $this->assertTrue($result->containsCode(30));
        
        $results = $result->getResults();
        
        $this->assertEquals(3, count($results));
        $this->assertEquals(2, $result->countErrors());
        $this->assertEquals(1, $result->countSuccesses());
        $this->assertEquals($subject->name, $results[0]->getSubject()->name);
    }
    
    public function test_collection_merge()
    {
        // we use an operation result as subject, as this
        // makes it easy to check if it's the right subject,
        // by using its unique ID.
        $subject = new OperationResult($this);
        
        $result1 = new OperationResult_Collection($subject);
        
        $result2 = new OperationResult_Collection(new stdClass());
        $result2->makeError('Error 1', 30);
        $result2->makeError('Error 2', 38);

        $result1->addResult($result2);
        
        $results = $result1->getResults();
        
        $this->assertEquals(2, $result1->countResults());
        $this->assertEquals(30, $result1->getCode());
        $this->assertFalse($result1->isValid());
        
        $first = array_shift($results);
        
        $this->assertInstanceOf(OperationResult::class, $first->getSubject());
        $this->assertEquals($subject->getID(), $first->getSubject()->getID());
    }
    
    public function test_collection_summary()
    {
        $result = new OperationResult_Collection(new stdClass());
        $result->makeError('Error 1', 30);
        $result->makeError('Error 2', 38);
        $result->makeNotice('Notice 1', 11);
        $result->makeWarning('Warning 1', 111);
        
        $summary = $result->getSummary();
        
        $this->assertStringContainsString('Warning 1', $summary);
        $this->assertStringContainsString('Error 1', $summary);
        $this->assertStringContainsString('Error 2', $summary);
        $this->assertStringContainsString('Notice 1', $summary);
        $this->assertStringContainsString('stdClass', $summary);
        $this->assertStringContainsString('Collection #'.$result->getID(), $summary);
    }
}
