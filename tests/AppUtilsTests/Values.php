<?php

use PHPUnit\Framework\TestCase;
use function AppUtils\valBool;
use function AppUtils\valBoolTrue;
use function AppUtils\valBoolFalse;

final class ReturnValuesTest extends TestCase
{
    public function test_boolDefault()
    {
        $bool = valBool();
        $this->assertFalse($bool->get());
        
        $bool = valBool(true);
        $this->assertTrue($bool->get());
    }

    public function test_boolSet()
    {
        $bool = valBool();
        $this->assertFalse($bool->get());
        
        $bool->set(true);
        $this->assertTrue($bool->get());
        
        $bool->set(false);
        $this->assertFalse($bool->get());
    }

    public function test_boolTrueDefault()
    {
        $bool = valBoolTrue();
        $this->assertFalse($bool->get());
        
        $bool = valBoolTrue(true);
        $this->assertTrue($bool->get());
    }
    
    public function test_boolTrueSet()
    {
        $bool = valBoolTrue();
        $this->assertFalse($bool->get());
        
        $bool->set(false);
        $this->assertFalse($bool->get());
        
        $bool->set(true);
        $this->assertTrue($bool->get());
        
        $bool->set(false);
        $this->assertTrue($bool->get());
    }
    
    public function test_boolFalseDefault()
    {
        $bool = valBoolFalse();
        $this->assertTrue($bool->get());
        
        $bool = valBoolFalse(false);
        $this->assertFalse($bool->get());
    }
    
    public function test_boolFalseSet()
    {
        $bool = valBoolFalse();
        $this->assertTrue($bool->get());
        
        $bool->set(true);
        $this->assertTrue($bool->get());
        
        $bool->set(false);
        $this->assertFalse($bool->get());
        
        $bool->set(true);
        $this->assertFalse($bool->get());
    }
}
