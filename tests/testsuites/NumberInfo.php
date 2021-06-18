<?php

use PHPUnit\Framework\TestCase;
use function AppUtils\parseNumber;

final class NumberInfoTest extends TestCase
{
    public function testParse()
    {
        $tests = array(
            array('value' => '', 'label' => 'Empty string', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '0', 'label' => 'String zero', 'units' => 'px', 'number' => 0, 'css' => '0'),
            array('value' => '1', 'label' => 'String one', 'units' => 'px', 'number' => 1, 'css' => '1px'),
            array('value' => 1, 'label' => 'Numeric one', 'units' => 'px', 'number' => 1, 'css' => '1px'),
            array('value' => 8.7, 'label' => 'Decimal value', 'units' => 'px', 'number' => 8, 'css' => '8px'),
            array('value' => 8.1, 'label' => 'Decimal value', 'units' => 'px', 'number' => 8, 'css' => '8px'),
            array('value' => '1,89', 'label' => 'String comma number', 'units' => 'px', 'number' => 1, 'css' => '1px'),
            array('value' => '10.42', 'label' => 'String dot number', 'units' => 'px', 'number' => 10, 'css' => '10px'),
            array('value' => null, 'label' => 'NULL value', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => false, 'label' => 'Boolean false', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => 'blabla', 'label' => 'String', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '1500', 'label' => 'String integer', 'units' => 'px', 'number' => 1500, 'css' => '1500px'),
            
            array('value' => '50%', 'label' => 'Percentage', 'units' => '%', 'number' => 50, 'css' => '50%'),
            array('value' => '15 px', 'label' => 'Pixel string with space', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15       px', 'label' => 'Pixel string with many spaces', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15px', 'label' => 'Pixel string without spaces', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15,45em', 'label' => 'EM float value with comma', 'units' => 'em', 'number' => 15.45, 'css' => '15.45em'),
            array('value' => '15.45em', 'label' => 'EM float value with dot', 'units' => 'em', 'number' => 15.45, 'css' => '15.45em'),
            array('value' => 'px', 'label' => 'Solo px units without number', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '0px', 'label' => 'Zero with px units', 'units' => 'px', 'number' => 0, 'css' => '0'),
            array('value' => 'blapx', 'label' => 'String with px units', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '    bla  px    ', 'label' => 'String with px units and spaces', 'units' => '', 'number' => 0, 'css' => ''),
            
            array('value' => array(), 'label' => 'Empty array', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => array(0), 'label' => 'Array', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => imagecreate(10, 10), 'label' => 'Resource', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => new DateTime(), 'label' => 'Object', 'units' => '', 'number' => 0, 'css' => ''),
        );
        
        $number = parseNumber(0);
        
        foreach($tests as $test)
        { 
            $number->setValue($test['value']);
            
            $this->assertSame($test['units'], $number->getUnits(), 'Units check: '.$test['label']);
            $this->assertSame($test['number'], $number->getNumber(), 'Number check: '.$test['label']);
            $this->assertSame($test['css'], $number->toCSS(), 'CSS check: '.$test['label']);
        }
    }

    public function test_isZero() : void
    {
        $this->assertTrue(parseNumber('0')->isZero(), 'String 0');
        $this->assertTrue(parseNumber(0)->isZero(), 'Int 0');
        $this->assertTrue(parseNumber(0.0)->isZero(), 'Float 0');

        $this->assertFalse(parseNumber('')->isZero(), 'Empty string');
        $this->assertFalse(parseNumber(null)->isZero(), 'NULL value');
    }

    public function test_isEmpty() : void
    {
        $this->assertFalse(parseNumber('0')->isEmpty(), 'String 0');
        $this->assertFalse(parseNumber(0)->isEmpty(), 'Int 0');
        $this->assertFalse(parseNumber(0.0)->isEmpty(), 'Float 0');

        $this->assertTrue(parseNumber('Invalid string')->isEmpty(), 'Invalid number');
        $this->assertTrue(parseNumber('')->isEmpty(), 'Empty string');
        $this->assertTrue(parseNumber(null)->isEmpty(), 'NULL value');
        $this->assertTrue(parseNumber('    \t \n')->isEmpty(), 'Whitespace only');
    }

    public function test_hasDecimals() : void
    {
        $this->assertFalse(parseNumber('0')->hasDecimals(), 'String 0');
        $this->assertFalse(parseNumber(0)->hasDecimals(), 'Int 0');
        $this->assertFalse(parseNumber(0.0)->hasDecimals(), 'Numeric 0.0');
        $this->assertFalse(parseNumber('15,00em')->hasDecimals(), 'String 15,00em');
        $this->assertFalse(parseNumber(array())->hasDecimals(), 'Empty array');
        $this->assertFalse(parseNumber(0.1)->hasDecimals(), 'Numeric 0.1 (unitless=pixels, cannot have decimals)');

        $this->assertTrue(parseNumber('55.20%')->hasDecimals(), 'String percentage');
        $this->assertTrue(parseNumber('0.1em')->hasDecimals(), 'Numeric 0.1em');
        $this->assertTrue(parseNumber('1.1em')->hasDecimals(), 'String 1.1');
        $this->assertTrue(parseNumber('15,21em')->hasDecimals(), 'String 15,2em');
    }

    public function test_isEven() : void
    {
        $this->assertFalse(parseNumber('')->isEven(), 'Empty string');
        $this->assertFalse(parseNumber(null)->isEven(), 'NULL value');
        $this->assertFalse(parseNumber(1)->isEven(), 'Int 1');
        $this->assertFalse(parseNumber('1.45em')->isEven(), 'String 1.45em');

        $this->assertTrue(parseNumber(0)->isEven(), 'Int zero');
        $this->assertTrue(parseNumber('0')->isEven(), 'String zero');
        $this->assertTrue(parseNumber('2')->isEven(), 'String two');
        $this->assertTrue(parseNumber('2.45em')->isEven(), 'String 2.45em');
    }

    public function test_hasUnits() : void
    {
        $this->assertFalse(parseNumber('')->hasUnits(), 'Empty string');
        $this->assertFalse(parseNumber(null)->hasUnits(), 'NULL value');
        $this->assertFalse(parseNumber(15)->hasUnits(), 'Int 15');
        $this->assertFalse(parseNumber('15')->hasUnits(), 'String 15');

        $this->assertTrue(parseNumber('15%')->hasUnits(), 'String 15%');
        $this->assertTrue(parseNumber('1.45em')->hasUnits(), 'String 1.45em');
    }

    public function test_setNumber() : void
    {
        $this->assertSame(0.8, parseNumber(0)->setNumber(0.8)->getNumber());
        $this->assertSame(5, parseNumber(0)->setNumber(5)->getNumber());
    }
}
