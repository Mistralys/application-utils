<?php

use PHPUnit\Framework\TestCase;

final class NumberInfoTest extends TestCase
{
    public function testParse()
    {
        $tests = array(
            array('value' => '', 'label' => 'Empty string', 'units' => null, 'number' => null, 'css' => null),
            array('value' => '0', 'label' => 'String zero', 'units' => null, 'number' => 0, 'css' => '0'),
            array('value' => '1', 'label' => 'String one', 'units' => null, 'number' => 1, 'css' => '1px'),
            array('value' => 1, 'label' => 'Numeric one', 'units' => null, 'number' => 1, 'css' => '1px'),
            array('value' => 8.7, 'label' => 'Decimal value', 'units' => null, 'number' => 8, 'css' => '8px'),
            array('value' => 8.1, 'label' => 'Decimal value', 'units' => null, 'number' => 8, 'css' => '8px'),
            array('value' => '1,89', 'label' => 'String comma number', 'units' => null, 'number' => 1, 'css' => '1px'),
            array('value' => '10.42', 'label' => 'String dot number', 'units' => null, 'number' => 10, 'css' => '10px'),
            array('value' => null, 'label' => 'null', 'units' => null, 'number' => null, 'css' => null),
            array('value' => false, 'label' => 'Boolean false', 'units' => null, 'number' => null, 'css' => null),
            array('value' => 'blabla', 'label' => 'String', 'units' => null, 'number' => null, 'css' => null),
            array('value' => '1500', 'label' => 'String integer', 'units' => null, 'number' => 1500, 'css' => '1500px'),
            
            array('value' => '50%', 'label' => 'Percentage', 'units' => '%', 'number' => 50, 'css' => '50%'),
            array('value' => '15 px', 'label' => 'Pixel string with space', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15       px', 'label' => 'Pixel string with many spaces', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15px', 'label' => 'Pixel string without spaces', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15,45em', 'label' => 'EM float value with comma', 'units' => 'em', 'number' => 15.45, 'css' => '15.45em'),
            array('value' => '15.45em', 'label' => 'EM float value with dot', 'units' => 'em', 'number' => 15.45, 'css' => '15.45em'),
            array('value' => 'px', 'label' => 'Solo px units without number', 'units' => 'px', 'number' => null, 'css' => null),
            array('value' => '0px', 'label' => 'Zero with px units', 'units' => 'px', 'number' => 0, 'css' => '0'),
            array('value' => 'blapx', 'label' => 'String with px units', 'units' => 'px', 'number' => null, 'css' => null),
            array('value' => '    bla  px    ', 'label' => 'String with px units and spaces', 'units' => 'px', 'number' => null, 'css' => null),
            
            array('value' => array(), 'label' => 'Empty array', 'units' => null, 'number' => null, 'css' => null),
            array('value' => array(0), 'label' => 'Array', 'units' => null, 'number' => null, 'css' => null),
            array('value' => imagecreate(10, 10), 'label' => 'Resource', 'units' => null, 'number' => null, 'css' => null),
            array('value' => new DateTime(), 'label' => 'Object', 'units' => null, 'number' => null, 'css' => null),
        );
        
        $number = AppUtils\parseNumber(0);
        
        foreach($tests as $test)
        { 
            $number->setValue($test['value']);
            
            $info = $number->getRawInfo();
            
            $this->assertEquals($info['units'], $test['units'], 'Units check: '.$test['label']);
            $this->assertEquals($info['number'], $test['number'], 'Number check: '.$test['label']);
            $this->assertEquals($number->toCSS(), $test['css'], 'CSS check: '.$test['label']);
        }
    }
}
