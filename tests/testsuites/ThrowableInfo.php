<?php

use PHPUnit\Framework\TestCase;
use AppUtils\ConvertHelper_ThrowableInfo;
use function AppUtils\parseThrowable;
use function AppUtils\restoreThrowable;

final class ThrowableInfoTest extends TestCase
{
    public function test_exceptionInfo()
    {
        try
        {
            throw new Exception(
                'Test message',
                12345
            );
        }
        catch(Exception $e)
        {
            $date = new DateTime();
            $date = $date->format('Y-m-d H');
            
            $info = parseThrowable($e);
            
            $this->assertEquals('Test message', $info->getMessage());
            $this->assertSame(12345, $info->getCode());
            $this->assertEquals(ConvertHelper_ThrowableInfo::CONTEXT_COMMAND_LINE, $info->getContext());
            $this->assertSame('', $info->getReferer());
            $this->assertSame($date, $info->getDate()->format('Y-m-d H'));
            
            $string = $info->toString();
            $count = $info->countCalls();
            
            $serialized = $info->serialize();
            
            $restored = restoreThrowable($serialized);
            
            $this->assertEquals('Test message', $restored->getMessage());
            $this->assertSame(12345, $restored->getCode());
            $this->assertEquals(ConvertHelper_ThrowableInfo::CONTEXT_COMMAND_LINE, $restored->getContext());
            $this->assertSame('', $restored->getReferer());
            $this->assertSame($count, $restored->countCalls());
            $this->assertSame($date, $restored->getDate()->format('Y-m-d H'));
            $this->assertEquals($string, $restored->toString());
        }
    }
    
    public function test_exceptionInfo_persist()
    {
        try
        {
            throw new Exception(
                'Test message',
                12345
            );
        }
        catch(Exception $e)
        {
            $info = parseThrowable($e);
            
            $serialized = $info->serialize();
            $string = $info->toString();
            
            $save = json_encode($serialized);
            $load = json_decode($save, true);
            
            $restored = restoreThrowable($serialized);
            
            $this->assertEquals('Test message', $restored->getMessage());
            $this->assertSame(12345, $restored->getCode());
            $this->assertEquals(ConvertHelper_ThrowableInfo::CONTEXT_COMMAND_LINE, $restored->getContext());
            $this->assertSame('', $restored->getReferer());
            $this->assertEquals($string, $restored->toString());
        }
    }
}
