<?php

use PHPUnit\Framework\TestCase;

use function AppUtils\sb;

final class StringBuilderTest extends TestCase
{
    public function test_sf() : void
    {
        $result = (string)sb()->sf('%1$s here and %2$s', 'One', 'Two');
        
        $this->assertEquals('One here and Two', $result);
    }
    
    public function test_translate() : void
    {
        $result = (string)sb()->t('Hello');
        
        $this->assertEquals('Hello', $result);
    }
    
    public function test_translate_params() : void
    {
        $result = (string)sb()->t('%1$s here and %2$s', 'One', 'Two');
        
        $this->assertEquals('One here and Two', $result);
    }
}
