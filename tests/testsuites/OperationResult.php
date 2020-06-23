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
    
    public function test_type_error()
    {
        $error = new OperationResult(new stdClass());
        $error->makeError('Error', 17);
        $this->assertTrue($error->isError());
        $this->assertEquals(OperationResult::TYPE_ERROR, $error->getType());
    }
    
    public function test_type_warning()
    {
        $warning = new OperationResult(new stdClass());
        $warning->makeWarning('Warning', 18);
        $this->assertTrue($warning->isWarning());
        $this->assertEquals(OperationResult::TYPE_WARNING, $warning->getType());
    }
    
    public function test_type_notice()
    {
        $notice = new OperationResult(new stdClass());
        $notice->makeNotice('Notice', 19);
        $this->assertTrue($notice->isNotice());
        $this->assertEquals(OperationResult::TYPE_NOTICE, $notice->getType());
    }
    
    public function test_type_success()
    {
        $success = new OperationResult(new stdClass());
        $success->makeSuccess('Success', 19);
        $this->assertTrue($success->isSuccess());
        $this->assertEquals(OperationResult::TYPE_SUCCESS, $success->getType());
    }
    
    public function test_collection_error()
    {
        $result = new OperationResult_Collection(new stdClass());
        $result->makeError('Error', 17);
        
        $items = $result->getErrors();
        
        $this->assertTrue($result->isError());
        $this->assertSame(1, $result->countErrors());
        $this->assertSame(1, count($items));
    }
    
    public function test_collection_warning()
    {
        $result = new OperationResult_Collection(new stdClass());
        $result->makeWarning('Warning', 17);
        
        $items = $result->getWarnings();
        
        $this->assertTrue($result->isWarning());
        $this->assertSame(1, $result->countWarnings());
        $this->assertSame(1, count($items));
    }
    
    public function test_collection_notice()
    {
        $result = new OperationResult_Collection(new stdClass());
        $result->makeNotice('Notice', 17);
        
        $items = $result->getNotices();
        
        $this->assertTrue($result->isNotice());
        $this->assertSame(1, $result->countNotices());
        $this->assertSame(1, count($items));
    }
    
    public function test_collection_success()
    {
        $result = new OperationResult_Collection(new stdClass());
        $result->makeSuccess('Success', 17);
        
        $items = $result->getSuccesses();
        
        $this->assertTrue($result->isSuccess());
        $this->assertSame(1, $result->countSuccesses());
        $this->assertSame(1, count($items));
    }
    
    public function test_collection_add()
    {
        $collection = new OperationResult_Collection(new stdClass());
        
        $notice = new OperationResult(new stdClass());
        $notice->makeNotice('Notice', 17);
        
        $collection->addResult($notice);
        
        $this->assertTrue($collection->isNotice());
        $this->assertSame(1, $collection->countNotices());
    }
}
