<?php

use PHPUnit\Framework\TestCase;

use AppUtils\ConvertHelper;

final class ConvertHelperTest extends TestCase
{
    /**
     * @see ConvertHelper::areVariablesEqual()
     */
    public function test_areVariablesEqual()
    {
        $tests = array(
            array('0', 0, true, 'String zero, numeric zero'),
            array('0', null, false, 'String zero, NULL'),
            array(null, 0, false, 'NULL, numeric zero'),
            array(false, null, false, 'FALSE, NULL'),
            array(false, '', false, 'FALSE, empty string'),
            array('1', 1, true, 'String 1, numeric 1'),
            array('112.58', 112.58, true, 'String float, numeric float'),
            array('', '', true, 'Empty string, empty string'),
            array('', null, true, 'Empty string, NULL'),
            array(null, null, true, 'NULL, NULL'),
            array('string', 'other', false, 'String, different string'),
            array('string', 'string', true, 'String, same string'),
            array(array('yo'), array('yo'), true, 'Array, same array'),
            array(array('yo'), array('no'), false, 'Array, different array'),
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::areVariablesEqual($test[0], $test[1]);
            
            $this->assertEquals($test[2], $result);
        }
    }
    
   /**
    * @see ConvertHelper::filenameRemoveExtension()
    */
    public function test_filenameRemoveExtension()
    {
        $tests = array(
            'somename.ext' => 'somename',
            '/path/to/file.txt' => 'file',
            'F:\\path\name.extension' => 'name',
            'With.Several.Dots.file' => 'With.Several.Dots',
            'noextension' => 'noextension',
            'file ending in dot.' => 'file ending in dot',
            '.ext' => ''
        );
        
        foreach($tests as $string => $expected) 
        {
            $actual = ConvertHelper::filenameRemoveExtension($string);
            
            $this->assertEquals($expected, $actual);
        }
    }
    
    /**
     * @see ConvertHelper::isStringHTML()
     */
    public function test_isStringHTML()
    {
        $tests = array(
            'Text without HTML' => false,
            'Text with < signs >' => false,
            'Text with <b>Some</b> HTML' => true,
            'Just a <br> single tag' => true,
            'Auto-closing <div/> here' => true,
            '' => false,
            '    ' => false,
            '>>>>' => false,
            '<!-- -->' => false,
            'Simple & ampersand' => false,
            'Encoded &amp; ampersand' => true
        );
        
        foreach($tests as $string => $expected)
        {
            $actual = ConvertHelper::isStringHTML($string);
            
            $this->assertEquals($expected, $actual);
        }
    }
    
    public function test_bool2string()
    {
        $tests = array(
            true => 'true',
            false => 'false',
            'true' => 'true',
            'false' => 'false',
            'yes' => 'true',
            'no' => 'false',
        );
        
        foreach($tests as $bool => $expected)
        {
            $actual = ConvertHelper::bool2string($bool);
            
            $this->assertEquals($expected, $actual);
        }
    }
    
    public function test_isStringASCII()
    {
        $tests = array(
            array('regular text', true, 'Regular text'),
            array('()?%$"46[]{}!+*', true, 'ASCII Characters'),
            array('A single ö', false, 'Special character'),
            array('', true, 'Empty string'),
            array(null, true, 'NULL'),
            array(array(), false, 'Array')
        );
        
        foreach($tests as $def)
        {
            $actual = ConvertHelper::isStringASCII($def[0]);
            
            $this->assertEquals($def[1], $actual, $def[2]);
        }
    }
}