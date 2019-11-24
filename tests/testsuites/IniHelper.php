<?php

use PHPUnit\Framework\TestCase;

use AppUtils\IniHelper;
use AppUtils\IniHelper_Exception;

final class IniHelperTest extends TestCase
{
    public function test_toArray_sectionless()
    {
        $iniString = 
"foo=bar
bar=foo";
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
        
        $parse = IniHelper::createFromString($iniString);
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
    
    public function test_toString_keepComments()
    {
        $iniString =
"; This is a comment

foo=bar";
        
        $parse = IniHelper::createFromString($iniString);
        $result = $parse->saveToString();
        
        $this->assertEquals($iniString, $result);
    }
    
    public function test_setValue_duplicateKeys()
    {
        $ini = IniHelper::createNew();
        
        $ini->setValue('foo', array('bar', 'foobar'));
        $amount = count($ini->getLinesByVariable('foo'));
        
        $this->assertEquals(2, $amount, 'Should be two lines for the foo variable.');
        $this->assertEquals(array('foo' => array('bar', 'foobar')), $ini->toArray());
        
        $expected = 
"foo=bar".$ini->getEOLChar().
"foo=foobar";
        
        $this->assertEquals($expected, $ini->saveToString());
    }
    
    public function test_setValue_nonScalar()
    {
        $ini = IniHelper::createNew();
        
        $this->expectException(IniHelper_Exception::class);
        
        $ini->setValue('foo', new stdClass());
    }
    
    public function test_addValue()
    {
        $ini = IniHelper::createNew();
       
        $ini->setValue('foo', array('bar', 'barfoo'));
        
        $ini->addValue('foo', 'foobar');
        
        $expected = array(
            'foo' => array(
                'bar',
                'barfoo',
                'foobar'
            )
        );
        
        $this->assertEquals($expected, $ini->toArray());
    }
    
    public function test_setValue_removeExisting()
    {
        $ini = IniHelper::createNew();
        
        $ini->setValue('foo', array('bar', 'barfoo', 'foobar'));
        
        $ini->setValue('foo', array('foobar', 'new'));
        
        $expected = array(
            'foo' => array(
                'foobar',
                'new'
            )
        );
        
        $this->assertEquals($expected, $ini->toArray());
    }
    
    public function test_setValue_replaceDuplicates()
    {
        $ini = IniHelper::createNew();
        
        $ini->setValue('foo', array('bar', 'foobar'));
        
        $ini->setValue('foo', 'single');
        
        $expected = array(
            'foo' => 'single'
        );
        
        $this->assertEquals($expected, $ini->toArray());
    }
}
