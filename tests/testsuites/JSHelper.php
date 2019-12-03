<?php

use PHPUnit\Framework\TestCase;

use AppUtils\JSHelper;

final class JSHelperTest extends TestCase
{
    public function test_phpVariable2JS()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'params' => '',
                'expected' => '""'
            ),
            array(
                'label' => 'Integer',
                'params' => 9,
                'expected' => '9'
            ),
            array(
                'label' => 'String',
                'params' => 'foo',
                'expected' => '"foo"'
            ),
            array(
                'label' => 'Boolean',
                'params' => false,
                'expected' => 'false'
            ),
            array(
                'label' => 'NULL',
                'params' => null,
                'expected' => 'null'
            ),
            array(
                'label' => 'Array',
                'params' => array('foo' => 'bar'),
                'expected' => json_encode(array('foo' => 'bar'))
            ),
            array(
                'label' => 'Object',
                'params' => new stdClass(),
                'expected' => '{}'
            ),
            array(
                'label' => 'Unicode character string',
                'params' => 'Über Ölß',
                'expected' => '"\u00dcber \u00d6l\u00df"'
            )
        );
        
        foreach($tests as $test)
        {
            $result = JSHelper::phpVariable2JS($test['params']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_phpVariable2JS_singleQuotes()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'params' => '',
                'expected' => "''"
            ),
            array(
                'label' => 'Integer',
                'params' => 9,
                'expected' => "9"
            ),
            array(
                'label' => 'String',
                'params' => 'foo',
                'expected' => "'foo'"
            ),
            array(
                'label' => 'Boolean',
                'params' => false,
                'expected' => "false"
            ),
            array(
                'label' => 'NULL',
                'params' => null,
                'expected' => "null"
            ),
            array(
                'label' => 'Array',
                'params' => array('foo' => 'bar'),
                'expected' => json_encode(array('foo' => 'bar'))
            ),
            array(
                'label' => 'Object',
                'params' => new stdClass(),
                'expected' => '{}'
            ),
            array(
                'label' => 'Unicode character string',
                'params' => 'Über Ölß',
                'expected' => "'\u00dcber \u00d6l\u00df'"
            )
        );
        
        foreach($tests as $test)
        {
            $result = JSHelper::phpVariable2JS($test['params'], JSHelper::QUOTE_STYLE_SINGLE);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_buildStatement()
    {
        $tests = array(
            array(
                'label' => 'No parameters',
                'params' => array(),
                'expected' => 'test();'
            ),
            array(
                'label' => 'Single string parameter',
                'params' => array('foo'),
                'expected' => 'test("foo");'
            ),
            array(
                'label' => 'Multiple parameters',
                'params' => array(9, "foo", true),
                'expected' => 'test(9,"foo",true);'
            ),
            array(
                'label' => 'Method name with dot',
                'params' => array('foo'),
                'method' => 'Foo.Bar',
                'expected' => 'Foo.Bar("foo");'
            ),
            array(
                'label' => 'Setting variable',
                'params' => array('foo'),
                'method' => 'var Foo = new Bar',
                'expected' => 'var Foo = new Bar("foo");'
            )
        );
        
        $callback = array(JSHelper::class, 'buildStatement');
        
        foreach($tests as $test)
        {
            $params = $test['params'];
            
            $method = 'test';
            if(isset($test['method'])) {
                $method = $test['method'];
            }
            
            array_unshift($params, $method);
            
            $result = call_user_func_array($callback, $params);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_buildVariable()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'name' => 'foo',
                'value' => '',
                'expected' => 'foo="";'
            ),
            array(
                'label' => 'Integer value',
                'name' => 'foo',
                'value' => 10,
                'expected' => 'foo=10;'
            ),
            array(
                'label' => 'String integer value',
                'name' => 'foo',
                'value' => '10',
                'expected' => 'foo="10";'
            ),
            array(
                'label' => 'Array value',
                'name' => 'foo',
                'value' => array('foo'),
                'expected' => 'foo=["foo"];'
            ),
            array(
                'label' => 'Boolean value',
                'name' => 'foo',
                'value' => false,
                'expected' => 'foo=false;'
            ),
            array(
                'label' => 'NULL value',
                'name' => 'foo',
                'value' => null,
                'expected' => 'foo=null;'
            ),
            array(
                'label' => 'With var prepended',
                'name' => 'var foo',
                'value' => 'bar',
                'expected' => 'var foo="bar";'
            ),
            array(
                'label' => 'With dot',
                'name' => 'window.document.foo',
                'value' => 'bar',
                'expected' => 'window.document.foo="bar";'
            )
        );
        
        foreach($tests as $test)
        {
            $result = JSHelper::buildVariable($test['name'], $test['value']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_nextElementID()
    {
        $counter = JSHelper::getElementCounter();
        
        $id = JSHelper::nextElementID();
        
        $this->assertEquals(JSHelper::getIDPrefix().($counter+1), $id, 'ID should match expected format.');
        $this->assertEquals(($counter+1), JSHelper::getElementCounter(), 'Counter should have increased by 1.');
    }
    
    public function test_nextElementID_idPrefix()
    {
        $counter = JSHelper::getElementCounter();
        
        JSHelper::setIDPrefix('FOO');
        $id = JSHelper::nextElementID();
        
        JSHelper::setIDPrefix('E');
        
        $this->assertEquals('FOO'.($counter+1), $id, 'Changing the prefix should have worked.');
    }
}
