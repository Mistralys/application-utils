<?php

declare(strict_types=1);

namespace AppUtilsTests\RequestTests;

use AppUtils\Request;
use AppUtilsTests\TestClasses\RequestTestCase;
use stdClass;

final class RegisteredParamTests extends RequestTestCase
{
    /**
     * Registering a parameter must return a parameter instance.
     *
     * @see Request::registerParam()
     */
    public function test_registerParam() : void
    {
        $request = new Request();

        $param = $request->registerParam('foo');

        $this->assertSame('foo', $param->getName());
    }

    /**
     * Registering a parameter without specifying a format
     * should act like getting it without registering it.
     *
     * @see Request::registerParam()
     */
    public function test_withoutType() : void
    {
        $request = new Request();

        $tests = array(
            array(
                'label' => 'Regular string',
                'value' => 'string',
                'expected' => 'string'
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'NULL',
                'value' => null,
                'expected' => null
            ),
            array(
                'label' => 'Zero',
                'value' => 0,
                'expected' => 0
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => '0'
            )
        );

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $this->assertEquals($def['expected'], $request->registerParam($name)->get(), $def['label']);
        }
    }

    /**
     * Fetching a valid integer value should return the
     * expected integer string.
     */
    public function test_integer() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => null
            ),
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => null
            ),
            array(
                'label' => 'String value',
                'value' => 'Not an integer',
                'expected' => null
            ),
            array(
                'label' => 'Array value',
                'value' => array(),
                'expected' => null
            ),
            array(
                'label' => 'Object value',
                'value' => new stdClass(),
                'expected' => null
            ),
            array(
                'label' => 'Numeric integer value',
                'value' => 8958,
                'expected' => 8958
            ),
            array(
                'label' => 'String integer value',
                'value' => '255',
                'expected' => 255
            ),
            array(
                'label' => 'Numeric float value',
                'value' => 14.8,
                'expected' => null
            )
        );

        $request = new Request();

        foreach($tests as $test)
        {
            $name = $this->setUniqueParam($test['value']);

            $value = $request->registerParam($name)->setInteger()->get();

            $this->assertSame($test['expected'], $value, $test['label']);
        }
    }

    public function test_numeric() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => null
            ),
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => null
            ),
            array(
                'label' => 'String value',
                'value' => 'Not an number',
                'expected' => null
            ),
            array(
                'label' => 'Array value',
                'value' => array(),
                'expected' => null
            ),
            array(
                'label' => 'Object value',
                'value' => new stdClass(),
                'expected' => null
            )
        );

        $request = new Request();

        foreach($tests as $test)
        {
            $name = $this->generateUniqueParamName();

            $value = $request->registerParam($name)->setInteger()->get();

            $this->assertSame($test['expected'], $value, $test['label']);
        }
    }

    /**
     * Specifying possible values should return only those values.
     */
    public function test_enum() : void
    {
        $tests = array(
            array(
                'label' => 'null value',
                'value' => null,
                'accepted' => array('bar', 'foo'),
                'expected' => null,
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'accepted' => array('bar', 'foo'),
                'expected' => null,
            ),
            array(
                'label' => 'Array value',
                'value' => array(),
                'accepted' => array('bar', 'foo'),
                'expected' => null,
            ),
            array(
                'label' => 'Zero value',
                'value' => 0,
                'accepted' => array('bar', 'foo'),
                'expected' => null,
            ),
            array(
                'label' => 'Value exists, and is in accepted values list.',
                'value' => 'bar',
                'accepted' => array('bar', 'foo', 'gnu'),
                'expected' => 'bar',
            ),
            array(
                'label' => 'Value exists, but is not in accepted values list.',
                'value' => 'bar',
                'accepted' => array('foo', 'gnu'),
                'expected' => null
            ),
            array(
                'label' => 'No value specified in request.',
                'value' => '',
                'accepted' => array('foo', 'bar'),
                'expected' => null
            ),
            array(
                'label' => 'The default value is used when an invalid value is specified.',
                'value' => 'invalid',
                'accepted' => array('foo', 'bar'),
                'expected' => 'foo',
                'default' => 'foo'
            ),
            array(
                'label' => 'The default value must also be in the accepted values.',
                'value' => 'invalid',
                'accepted' => array('foo', 'bar'),
                'expected' => null,
                'default' => 'invalid'
            )
        );

        $request = new Request();

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $default = $def['default'] ?? null;

            $value = $request->registerParam($name)
                ->setEnum($def['accepted'])
                ->get($default);

            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_filter_stripWhitespace() : void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => ''
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo'),
                'expected' => ''
            ),
            array(
                'label' => 'Object value',
                'value' => new stdClass(),
                'expected' => ''
            ),
            array(
                'label' => 'Single space',
                'value' => ' ',
                'expected' => ''
            ),
            array(
                'label' => 'Texts with several spaces between',
                'value' => 'foo       bar',
                'expected' => 'foobar'
            ),
            array(
                'label' => 'Text with spaces around it',
                'value' => '   foo   ',
                'expected' => 'foo'
            ),
            array(
                'label' => 'Text with tabs and newlines',
                'value' => "\t foo \r \n",
                'expected' => 'foo'
            )
        );

        $request = new Request();

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $value = $request->registerParam($name)
                ->addStripWhitespaceFilter()
                ->get('');

            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_idsList() : void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => array()
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => array()
            ),
            array(
                'label' => 'Invalid string value',
                'value' => 'invalid',
                'expected' => array()
            ),
            array(
                'label' => 'Single ID value',
                'value' => '5',
                'expected' => array(5)
            ),
            array(
                'label' => 'Single ID value with spaces around it',
                'value' => '   5   ',
                'expected' => array(5)
            ),
            array(
                'label' => 'Multiple ID values',
                'value' => '5,14,20,79',
                'expected' => array(5, 14, 20, 79)
            ),
            array(
                'label' => 'Stripping whitespace',
                'value' => '5,    89    , 21',
                'expected' => array(5, 89, 21)
            ),
            array(
                'label' => 'Mixing valid and invalid values',
                'value' => '5, invalid, something, 50',
                'expected' => array(5, 50)
            ),
            array(
                'label' => 'List with newlines and tabs',
                'value' => "\t5,\n\t50\n",
                'expected' => array(5, 50)
            ),
        );

        $request = new Request();

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $value = $request->registerParam($name)
                ->setIDList()
                ->get();

            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_combination_idlist_enum() : void
    {
        $request = new Request();

        $name = $this->setUniqueParam('5,89,111');

        $value = $request->registerParam($name)
            ->setIDList()
            ->setEnum(array(89, 5))
            ->get();

        $this->assertEquals(array(5, 89), $value, 'Combination failed');
    }

    public function test_combination_idlist_callback() : void
    {
        $request = new Request();

        $name = $this->setUniqueParam('5,89,111');

        $value = $request->registerParam($name)
            ->setIDList()
            ->setCallback(
                function($value)
                {
                    return $value === 5;
                }
            )
            ->get();

        $this->assertEquals(array(5), $value, 'Combination failed');
    }

    public function test_combination_idlist_valuesList() : void
    {
        $request = new Request();

        $name = $this->setUniqueParam('5,89,111');

        $value = $request->registerParam($name)
            ->setIDList()
            ->setValuesList(array(89))
            ->get();

        $this->assertEquals(array(89), $value, 'Combination failed');
    }

    public function test_combination_comma_separated_valuesList() : void
    {
        $request = new Request();

        $name = $this->setUniqueParam('bar,lopos,foo');

        $value = $request->registerParam($name)
            ->addCommaSeparatedFilter()
            ->setValuesList(array('foo', 'bar'))
            ->get();

        $this->assertEquals(array('bar', 'foo'), $value, 'Combination failed');
    }

    public function test_boolean() : void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => false
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => false
            ),
            array(
                'label' => 'Invalid string value',
                'value' => 'invalid',
                'expected' => false
            ),
            array(
                'label' => 'Valid string false value',
                'value' => 'false',
                'expected' => false
            ),
            array(
                'label' => 'Valid string true value',
                'value' => 'true',
                'expected' => true
            ),
            array(
                'label' => 'Valid string true value, alternate yes/no',
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'label' => 'Array value',
                'value' => array(),
                'expected' => false
            ),
        );

        $request = new Request();

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $value = $request->registerParam($name)
                ->setBoolean()
                ->get();

            $this->assertSame($def['expected'], $value, $def['label']);
        }
    }

    public function test_commaSeparated() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'value' => '',
                'allowed' => array('foo', 'bar'),
                'expected' => array(),
            ),
            array(
                'label' => 'null value',
                'value' => null,
                'allowed' => array('foo', 'bar'),
                'expected' => array(),
            ),
            array(
                'label' => 'Empty array value',
                'value' => array(),
                'allowed' => array('foo', 'bar'),
                'expected' => array(),
            ),
            array(
                'label' => 'Pre-filled array',
                'value' => array('foo'),
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo'),
            ),
            array(
                'label' => 'Object value',
                'value' => new stdClass(),
                'allowed' => array('foo', 'bar'),
                'expected' => array(),
            ),
            array(
                'label' => 'Zero value',
                'value' => 0,
                'allowed' => array('foo', 'bar'),
                'expected' => array(),
            ),
            array(
                'label' => 'Comma separated values',
                'value' => 'foo,bar,lopos',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo', 'bar'),
            ),
            array(
                'label' => 'Comma separated values, with empty entries',
                'value' => 'foo,bar,lopos,,,',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo', 'bar'),
            ),
            array(
                'label' => 'Comma separated values, with trim OFF',
                'value' => 'foo,  bar,  lopos',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo'),
                'trim' => false
            ),
            array(
                'label' => 'Comma separated values, with strip OFF',
                'value' => 'foo,bar, ,lopos',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo', 'bar'),
                'strip' => false
            )
        );

        $request = new Request();

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $trim = $def['trim'] ?? true;
            $strip = $def['strip'] ?? true;

            $value = $request->registerParam($name)
                ->addCommaSeparatedFilter($trim, $strip)
                ->setValuesList($def['allowed'])
                ->get();

            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_regex() : void
    {
        $tests = array(
            array(
                'label' => 'Null value',
                'value' => null,
                'expected' => '',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => '',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo'),
                'expected' => '',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
            array(
                'label' => 'Object value',
                'value' => new stdClass(),
                'expected' => '',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
            array(
                'label' => 'Numeric Zero',
                'value' => 0,
                'expected' => '0',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
            array(
                'label' => 'Invalid string',
                'value' => '*-++**',
                'expected' => '',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
            array(
                'label' => 'Boolean true',
                'value' => true,
                'expected' => '',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
            array(
                'label' => 'Simple alnum regex',
                'value' => 'FooBar2',
                'expected' => 'FooBar2',
                'regex' => '/[a-zA-Z0-9]+/'
            ),
        );

        $request = new Request();

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $value = $request->registerParam($name)
                ->setRegex($def['regex'])
                ->get();

            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_url() : void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => ''
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo' => 'bar'),
                'expected' => ''
            ),
            array(
                'label' => 'Invalid url value',
                'value' => 'invalid',
                'expected' => ''
            ),
            array(
                'label' => 'Valid url value',
                'value' => 'https://www.foo.com',
                'expected' => 'https://www.foo.com'
            )
        );

        $request = new Request();

        $value = $request->registerParam(uniqid('', true))
            ->setURL()
            ->get();

        $this->assertSame('', $value, 'Parameter not present at all');

        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $value = $request->registerParam($name)
                ->setURL()
                ->get();

            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_combination_comma_separated_callback() : void
    {
        $request = new Request();

        $name = $this->setUniqueParam('bar,lopos,foo');

        $value = $request->registerParam($name)
            ->addCommaSeparatedFilter()
            ->setCallback(function($value) {
                return in_array('lopos', $value, true);
            })
            ->get();

        $this->assertEquals(array('bar', 'lopos', 'foo'), $value, 'Combination failed');
    }

    public function test_getTyped() : void
    {
        $request = new Request();

        $this->assertSame(
            'string',
            $request
                ->registerParam($this->setUniqueParam('string'))
                ->getString()
        );

        $this->assertSame(
            42,
            $request
                ->registerParam($this->setUniqueParam('42'))
                ->getInt()
        );

        $this->assertSame(
            42.66,
            $request
                ->registerParam($this->setUniqueParam('42.66'))
                ->getFloat()
        );

        $this->assertTrue(
            $request
                ->registerParam($this->setUniqueParam('yes'))
                ->getBool()
        );
    }

    public function test_getRegisteredParam() : void
    {
        $request = new Request();
        $request->registerParam('foo');

        $this->assertSame('foo', $request->getRegisteredParam('foo')->getName());
    }

    public function test_getRegisteredParamNotFound() : void
    {
        $request = new Request();

        $this->expectExceptionCode(Request::ERROR_PARAM_NOT_REGISTERED);

        $request->getRegisteredParam('foo');
    }
}
