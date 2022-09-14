<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\ConvertHelper;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use stdClass;
use TestClasses\BaseTestCase;

class JSONConverterTest extends BaseTestCase
{
    public function test_var2json() : void
    {
        $tests = array(
            array(
                'value' => '',
                'expected' => '""'
            ),
            array(
                'value' => true,
                'expected' => 'true'
            ),
            array(
                'value' => 14,
                'expected' => '14'
            ),
            array(
                'value' => array('foo' => 'bar'),
                'expected' => '{"foo":"bar"}'
            )
        );

        foreach ($tests as $test)
        {
            $this->assertSame($test['expected'], JSONConverter::var2json($test['value']));
        }
    }

    public function test_error() : void
    {
        $this->expectException(JSONConverterException::class);

        JSONConverter::var2json(NAN);
    }

    public function test_var2jsonSilent() : void
    {
        $this->assertSame('', JSONConverter::var2jsonSilent(NAN));
    }

    public function test_json2var() : void
    {
        $this->assertNull(JSONConverter::json2var(''));

        $this->assertSame(
            array('foo' => 'bar'),
            JSONConverter::json2var('{"foo":"bar"}')
        );

        $this->assertTrue(JSONConverter::json2var('true'));
    }

    public function test_json2varSilent() : void
    {
        $this->assertNull(JSONConverter::json2varSilent('Not valid JSON'));
    }

    public function test_json2varObject() : void
    {
        $this->assertInstanceOf(
            stdClass::class,
            JSONConverter::json2var(
                JSONConverter::var2json(new stdClass()),
                false
            )
        );
    }
}
