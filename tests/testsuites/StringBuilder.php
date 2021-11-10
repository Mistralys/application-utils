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

    public function test_ifTrue() : void
    {
        $this->assertEquals('Test', (string)sb()->ifTrue(true, 'Test'));
        $this->assertEquals('', (string)sb()->ifTrue(false, 'Test'));
    }

    public function test_ifTrue_emptyString() : void
    {
        $this->assertEquals('', (string)sb()->ifTrue(true, null));
        $this->assertEquals('', (string)sb()->ifTrue(true, ''));
    }

    public function test_ifFalse() : void
    {
        $this->assertEquals('Test', (string)sb()->ifFalse(false, 'Test'));
        $this->assertEquals('', (string)sb()->ifFalse(true, 'Test'));
    }

    public function test_ifEmpty() : void
    {
        $this->assertEquals('Test', (string)sb()->ifEmpty('', 'Test'));
        $this->assertEquals('', (string)sb()->ifEmpty('Not empty', 'Test'));
    }

    public function test_ifEmpty_objectNullable() : void
    {
        $subject = null;

        $this->assertEquals('', (string)sb()->ifEmpty($subject, $subject));
    }

    public function test_ifNotEmpty() : void
    {
        $this->assertEquals('Test', (string)sb()->ifNotEmpty('Not empty', 'Test'));
        $this->assertEquals('', (string)sb()->ifNotEmpty('', 'Test'));
    }
}
