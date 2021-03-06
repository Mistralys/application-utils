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

    public function test_para() : void
    {
        $this->assertEquals('<br><br>', (string)sb()->para());

        $this->assertEquals('<p>Test</p>', (string)sb()->para('Test'));
    }

    public function test_nospace() : void
    {
        $this->assertEquals('Test', (string)sb()->nospace('Test'));

        $this->assertEquals('TestFoo', (string)sb()->nospace('Test')->nospace('Foo'));

        $this->assertEquals('Test YoFoo', (string)sb()->nospace('Test')->add('Yo')->nospace('Foo'));
    }
}
