<?php

declare(strict_types=1);

namespace AppUtilsTests;

use PHPUnit\Framework\TestCase;
use AppUtils\IniHelper;
use AppUtils\IniHelper_Exception;
use AppUtils\IniHelper_Section;
use stdClass;

final class IniHelperTest extends TestCase
{
    public function test_toArray_sectionless(): void
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

    public function test_toArray_singlesection(): void
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

    public function test_toArray_duplicateKeys(): void
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

    public function test_toArray_invalidLine(): void
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

    public function test_toArray_quoted(): void
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

    public function test_toArray_setValue_newValue(): void
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

    public function test_toArray_setValue_overwrite(): void
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

    public function test_toArray_setValue_newSection(): void
    {
        $iniString =
            "foo=bar";

        $parse = IniHelper::createFromString($iniString);
        $parse->setValue('section/bar', 'foo');
        $result = $parse->toArray();

        $expected = array(
            'foo' => 'bar',
            'section' => array(
                'bar' => 'foo'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function test_toArray_setValue_existingSection(): void
    {
        $iniString =
            "foo=bar
[section]
bar=foo";

        $parse = IniHelper::createFromString($iniString);
        $parse->setValue('section/bar', 'foobar');
        $result = $parse->toArray();

        $expected = array(
            'foo' => 'bar',
            'section' => array(
                'bar' => 'foobar'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function test_toString_keepComments(): void
    {
        $iniString =
            "; This is a comment

foo=bar";

        $parse = IniHelper::createFromString($iniString);
        $result = $parse->saveToString();

        $this->assertEquals($iniString, $result);
    }

    public function test_setValue_duplicateKeys(): void
    {
        $ini = IniHelper::createNew();

        $ini->setValue('foo', array('bar', 'foobar'));
        $amount = count($ini->getLinesByVariable('foo'));

        $this->assertEquals(2, $amount, 'Should be two lines for the foo variable.');
        $this->assertEquals(array('foo' => array('bar', 'foobar')), $ini->toArray());

        $expected =
            "foo=bar" . $ini->getEOLChar() .
            "foo=foobar";

        $this->assertEquals($expected, $ini->saveToString());
    }

    public function test_setValue_nonScalar(): void
    {
        $ini = IniHelper::createNew();

        $this->expectException(IniHelper_Exception::class);

        $ini->setValue('foo', new stdClass());
    }

    public function test_addValue(): void
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

    public function test_setValue_removeExisting(): void
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

    public function test_setValue_replaceDuplicates(): void
    {
        $ini = IniHelper::createNew();

        $ini->setValue('foo', array('bar', 'foobar'));

        $ini->setValue('foo', 'single');

        $expected = array(
            'foo' => 'single'
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_getSection_dynamic(): void
    {
        $ini = IniHelper::createNew();

        $this->assertNull($ini->getSection('section'));

        $ini->setValue('section/foo', 'bar');

        $this->assertInstanceOf(IniHelper_Section::class, $ini->getSection('section'));
    }

    public function test_getSection_iniString(): void
    {
        $iniContent =
            "[section]
foo=bar";

        $ini = IniHelper::createFromString($iniContent);

        $this->assertInstanceOf(IniHelper_Section::class, $ini->getSection('section'));
    }

    public function test_setValue_quoted_double(): void
    {
        $ini = IniHelper::createNew();

        $ini->setValue('foo', '"    bar    "');

        $expected = array(
            'foo' => '    bar    '
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_setValue_quoted_single(): void
    {
        $ini = IniHelper::createNew();

        $ini->setValue('foo', "'    bar    '");

        $expected = array(
            'foo' => '    bar    '
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_sectionName_trim(): void
    {
        $ini = IniHelper::createNew();

        $ini->setValue('   foo   ', 'bar');

        $expected = array(
            'foo' => 'bar'
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_sectionName_trimIniString(): void
    {
        $iniContent =
            "   [   section   ]   
foo=bar";

        $ini = IniHelper::createFromString($iniContent);

        $expected = array(
            'section' => array(
                'foo' => 'bar'
            )
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_sectionName_trimValues(): void
    {
        $iniContent =
            "    foo    =    bar     
    bar = foo";

        $ini = IniHelper::createFromString($iniContent);

        $expected = array(
            'foo' => 'bar',
            'bar' => 'foo'
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_sectionName_dots_iniString(): void
    {
        $iniContent =
            "foo.bar=foobar
[section]
foo.bar.bar=foobar";

        $ini = IniHelper::createFromString($iniContent);

        $expected = array(
            'foo.bar' => 'foobar',
            'section' => array(
                'foo.bar.bar' => 'foobar'
            )
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_sectionName_dots_dynamic(): void
    {
        $ini = IniHelper::createNew();

        $ini->setValue('foo.bar', 'foobar');

        $ini->setValue('section/foo.bar.bar', 'foobar');

        $expected = array(
            'foo.bar' => 'foobar',
            'section' => array(
                'foo.bar.bar' => 'foobar'
            )
        );

        $this->assertEquals($expected, $ini->toArray());
    }

    public function test_sectionName_withDots(): void
    {
        $ini = IniHelper::createNew();

        $ini->setValue('section.with.dots/foo', 'bar');

        $expected = array(
            'section.with.dots' => array(
                'foo' => 'bar'
            )
        );

        $this->assertEquals($expected, $ini->toArray());
    }
}
