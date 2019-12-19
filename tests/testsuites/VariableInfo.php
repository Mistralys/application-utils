<?php

use PHPUnit\Framework\TestCase;
use AppUtils\VariableInfo;
use function AppUtils\parseVariable;

final class VariableInfoTest extends TestCase
{
    public function test_variables()
    {
        $tests = array(
            array(
                'value' => array('foo' => 'bar'),
                'type' => VariableInfo::TYPE_ARRAY,
                'string' => json_encode(array('foo' => 'bar'), JSON_PRETTY_PRINT)
            ),
            array(
                'value' => true,
                'type' => VariableInfo::TYPE_BOOLEAN,
                'string' => 'true'
            ),
            array(
                'value' => 1,
                'type' => VariableInfo::TYPE_INTEGER,
                'string' => '1'
            ),
            array(
                'value' => 14.11,
                'type' => VariableInfo::TYPE_DOUBLE,
                'string' => '14.11'
            ),
            array(
                'value' => new \VariableInfoTest_DummyClass(),
                'type' => VariableInfo::TYPE_OBJECT,
                'string' => 'VariableInfoTest_DummyClass'
            ),
            array(
                'value' => 'Text',
                'type' => VariableInfo::TYPE_STRING,
                'string' => 'Text'
            ),
            array(
                'value' => null,
                'type' => VariableInfo::TYPE_NULL,
                'string' => 'null'
            ),
            array(
                'value' => imagecreate(1, 1),
                'type' => VariableInfo::TYPE_RESOURCE,
                'string' => null
            ),
            array(
                'value' => function() {},
                'type' => VariableInfo::TYPE_OBJECT,
                'string' => 'Closure'
            ),
            array(
                'value' => array($this, 'dummyMethod'),
                'type' => VariableInfo::TYPE_CALLABLE,
                'string' => 'VariableInfoTest->dummyMethod()'
            ),
            array(
                'value' => array('VariableInfoTest', 'dummyMethod'),
                'type' => VariableInfo::TYPE_CALLABLE,
                'string' => 'VariableInfoTest::dummyMethod()'
            ),
        );
        
        foreach($tests as $def)
        {
            $var = parseVariable($def['value']);
            
            $this->assertEquals($def['type'], $var->getType(), 'The type does not match');
            
            if($def['string'] !== null) {
                $this->assertEquals($def['string'], $var->toString(), 'The toString() result does not match');
            }
        }
    }

    public function test_enableType()
    {
        $tests = array(
            array(
                'label' => 'null value',
                'value' => null,
                'string' => 'null',
                'type' => VariableInfo::TYPE_NULL
            ),
            array(
                'label' => 'String value',
                'value' => 'Test text',
                'string' => 'string "Test text"',
                'type' => VariableInfo::TYPE_STRING
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo' => 'bar'),
                'string' => 'array '.json_encode(array('foo' => 'bar'), JSON_PRETTY_PRINT),
                'type' => VariableInfo::TYPE_ARRAY
            ),
            array(
                'label' => 'Integer value',
                'value' => 1,
                'string' => 'integer 1',
                'type' => VariableInfo::TYPE_INTEGER
            ),
            array(
                'label' => 'double value',
                'value' => 1.54,
                'string' => 'double 1.54',
                'type' => VariableInfo::TYPE_DOUBLE
            ),
            array(
                'label' => 'class value',
                'value' => new \VariableInfoTest_DummyClass(),
                'string' => 'object VariableInfoTest_DummyClass',
                'type' => VariableInfo::TYPE_OBJECT
            ),
            array(
                'label' => 'callback value',
                'value' => array($this, 'dummyMethod'),
                'string' => 'callable VariableInfoTest->dummyMethod()',
                'type' => VariableInfo::TYPE_CALLABLE
            ),
            array(
                'label' => 'resource value',
                'value' => imagecreate(10, 10),
                'string' => 'resource #',
                'type' => VariableInfo::TYPE_RESOURCE
            )
        );
        
        foreach($tests as $def)
        {
            $var = parseVariable($def['value']);
            $var->enableType();
            
            $length = strlen($def['string']);
            
            $this->assertEquals($def['type'], $var->getType(), $def['label']);
            $this->assertEquals($def['string'], substr($var->toString(), 0, $length), $def['label']);
        }
    }
    
    public function test_serialize()
    {
        $tests = array(
            array(
                'label' => 'null value',
                'value' => null,
                'string' => 'null',
            ),
            array(
                'label' => 'String value',
                'value' => 'Test text',
                'string' => 'Test text'
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo' => 'bar'),
                'string' => json_encode(array('foo' => 'bar'), JSON_PRETTY_PRINT)
            ),
            array(
                'label' => 'Integer value',
                'value' => 1,
                'string' => '1'
            ),
            array(
                'label' => 'double value',
                'value' => 1.54,
                'string' => '1.54'
            ),
            array(
                'label' => 'class value',
                'value' => new \VariableInfoTest_DummyClass(),
                'string' => 'VariableInfoTest_DummyClass'
            ),
            array(
                'label' => 'callback value',
                'value' => array($this, 'dummyMethod'),
                'string' => 'VariableInfoTest->dummyMethod()'
            ),
        );
        
        foreach($tests as $def)
        {
            $var = parseVariable($def['value']);
            
            $this->assertEquals($def['string'], $var->toString(), $def['label']);

            $serialized = $var->serialize();
            
            $restored = VariableInfo::fromSerialized($serialized);
            
            $this->assertEquals($def['string'], $restored->toString(), $def['label']);
        }
    }
    
    public static function dummyMethod()
    {
        
    }
}

class VariableInfoTest_DummyClass
{
    
}