<?php

use PHPUnit\Framework\TestCase;

use AppUtils\IniHelper;

final class IniHelperTest extends TestCase
{
    public function test_toArray_sectionless()
    {
        $iniString = 
"foo=bar
bar=foo";
        
        $parse = IniHelper::fromString($iniString);
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => 'bar', 
            'bar' => 'foo'
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_singlesection()
    {
        $iniString =
"[section]
foo=bar
bar=foo";
        
        $parse = IniHelper::fromString($iniString);
        $result = $parse->toArray();
        
        $expected = array(
            'section' => array(
                'foo' => 'bar', 
                'bar' => 'foo'
            )
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_duplicateKeys()
    {
        $iniString =
"foo=bar
foo=foobar
foo=barfoo";
        
        $parse = IniHelper::fromString($iniString);
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => array(
                'bar',
                'foobar',
                'barfoo'
            )
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_invalidLine()
    {
        $iniString =
"foo=bar
foobar:something
bar=foo";
        
        $parse = IniHelper::fromString($iniString);
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => 'bar',
            'bar' => 'foo'
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_quoted()
    {
        $iniString =
'foo="bar"
name="    with spaces    "
bar=foo';
        
        $parse = IniHelper::fromString($iniString);
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => 'bar',
            'name' => '    with spaces    ',
            'bar' => 'foo'
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_setValue_newValue()
    {
        $iniString =
        "foo=bar";
        
        $parse = IniHelper::fromString($iniString);
        $parse->setValue('bar', 'foo');
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => 'bar',
            'bar' => 'foo'
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_setValue_overwrite()
    {
        $iniString =
        "foo=bar";
        
        $parse = IniHelper::fromString($iniString);
        $parse->setValue('foo', 'foobar');
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => 'foobar'
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_setValue_newSection()
    {
        $iniString =
        "foo=bar";
        
        $parse = IniHelper::fromString($iniString);
        $parse->setValue('section.bar', 'foo');
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => 'bar',
            'section' => array(
                'bar' => 'foo'
            )
        );
        
        $this->assertEquals($expected, $result);
    }
    
    public function test_toArray_setValue_existingSection()
    {
        $iniString =
"foo=bar
[section]
bar=foo";
        
        $parse = IniHelper::fromString($iniString);
        $parse->setValue('section.bar', 'foobar');
        $result = $parse->toArray();
        
        $expected = array(
            'foo' => 'bar',
            'section' => array(
                'bar' => 'foobar'
            )
        );
        
        $this->assertEquals($expected, $result);
    }
}
