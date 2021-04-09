<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Array;
use PHPUnit\Framework\TestCase;

final class ConvertHelper_ArrayTest extends TestCase
{
    public function test_removeValues() : void
    {
        $input = array(
            'val1',
            'val2',
            'val3',
            'val1'
        );

        $remove = array(
            'val1'
        );

        $expected = array(
            'val2',
            'val3'
        );

        $this->assertEquals($expected, ConvertHelper::arrayRemoveValues($input, $remove));
    }

    public function test_removeValuesAssoc() : void
    {
        $input = array(
            'key1' => 'val1',
            'key2' => 'val2',
            'key3' => 'val3',
        );

        $remove = array(
            'val2'
        );

        $expected = array(
            'key1' => 'val1',
            'key3' => 'val3',
        );

        $this->assertEquals($expected, ConvertHelper::arrayRemoveValues($input, $remove, true));
        $this->assertEquals($expected, ConvertHelper_Array::removeValuesAssoc($input, $remove));
    }

    public function test_removeValues_removeIsAssociative() : void
    {
        $input = array(
            'val1',
            'val2',
            'val3',
        );

        $remove = array(
            'key1' => 'val2'
        );

        $expected = array(
            'val1',
            'val3',
        );

        $this->assertEquals($expected, ConvertHelper::arrayRemoveValues($input, $remove));
    }

    public function test_removeKeys() : void
    {
        $tests = array(
            array(
                'label' => 'Keys not present in target array',
                'value' => array('bar' => 'foo'),
                'remove' => array('foo'),
                'expected' => array('bar' => 'foo')
            ),
            array(
                'label' => 'Remove assoc keys',
                'value' => array('foo' => 'bar', 'bar' => 'foo'),
                'remove' => array('foo', 'bar'),
                'expected' => array()
            ),
            array(
                'label' => 'Remove numeric keys',
                'value' => array('foo' => 'bar', 20 => 'foo'),
                'remove' => array(20),
                'expected' => array('foo' => 'bar')
            ),
            array(
                'label' => 'Loose key typing',
                'value' => array('foo' => 'bar', '20' => 'foo'),
                'remove' => array(20),
                'expected' => array('foo' => 'bar')
            ),
            array(
                'label' => 'Empty remove array',
                'value' => array('foo' => 'bar'),
                'remove' => array(),
                'expected' => array('foo' => 'bar')
            )
        );

        foreach($tests as $test)
        {
            $array = $test['value'];

            ConvertHelper::arrayRemoveKeys($array, $test['remove']);

            $this->assertEquals($test['expected'], $array, $test['label']);
        }
    }

    public function test_toAttributeString() : void
    {
        $input = array(
            'key1' => 'value',
            'null' => null,
            'empty' => '',
            'space' => ' ',
            'html' => '<bold></bold>',
            'quotes' => '"text"',
            'special' => 'öäü'
        );

        $this->assertEquals(
            ' key1="value" space=" " html="&lt;bold&gt;&lt;/bold&gt;" quotes="&quot;text&quot;" special="öäü"',
            ConvertHelper::array2attributeString($input)
        );
    }

    public function test_implodeWithAnd() : void
    {
        $tests = array(
            array(
                'label' => 'With spaces',
                'source' => array('One', 'Two', 'Three'),
                'sep' => ',',
                'conjunction' => '|',
                'expected' => 'One,Two|Three'
            ),
            array(
                'label' => 'With spaces',
                'source' => array('One', 'Two', 'Three'),
                'sep' => '; ',
                'conjunction' => ' | ',
                'expected' => 'One; Two | Three'
            ),
            array(
                'label' => 'With conjunction',
                'source' => array('One', 'Two', 'Three'),
                'sep' => ', ',
                'conjunction' => ' or ',
                'expected' => 'One, Two or Three'
            )
        );

        foreach ($tests as $test)
        {
            $result = ConvertHelper_Array::implodeWithAnd($test['source'], $test['sep'], $test['conjunction']);
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
}
