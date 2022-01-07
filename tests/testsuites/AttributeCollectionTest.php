<?php

declare(strict_types=1);

use AppUtils\AttributeCollection;
use PHPUnit\Framework\TestCase;
use function AppUtils\sb;

final class AttributeCollectionTest extends TestCase
{
    public function test_initialAttributes() : void
    {
        $attribs = AttributeCollection::create(array(
            'name' => 'Name',
            'class' => '    class-one class-two  '
        ));

        $this->assertTrue($attribs->hasClasses());
        $this->assertTrue($attribs->hasClass('class-one'));
        $this->assertTrue($attribs->hasClass('class-two'));
        $this->assertEquals('Name', $attribs->getAttribute('name'));
    }

    public function test_hasAttribute() : void
    {
        $attribs = AttributeCollection::create();

        $this->assertFalse($attribs->hasAttribute('test'));

        $attribs->attr('test', null);
        $this->assertFalse($attribs->hasAttribute('test'));

        $attribs->attr('test', '');
        $this->assertFalse($attribs->hasAttribute('test'));

        $attribs->attr('test', false);
        $this->assertTrue($attribs->hasAttribute('test'));
    }

    public function test_removeAttribute() : void
    {
        $attribs = AttributeCollection::create();

        $attribs->attr('test', 'value');
        $this->assertTrue($attribs->hasAttribute('test'));

        $attribs->remove('test');
        $this->assertFalse($attribs->hasAttribute('test'));
    }

    public function test_escapeQuotes() : void
    {
        $attribs = AttributeCollection::create();

        $attribs->attrQuotes('test', 'Label with "Quotes"');

        $this->assertEquals('Label with &quot;Quotes&quot;', $attribs->getAttribute('test'));
    }

    public function test_href() : void
    {
        $attribs = AttributeCollection::create();

        $attribs->href('https://testdomain.com?param=value&foo=bar&amp;preencoded=true');

        $this->assertEquals('https://testdomain.com?param=value&amp;foo=bar&amp;preencoded=true', $attribs->getAttribute('href'));
    }

    public function test_variableTypes() : void
    {
        $attribs = AttributeCollection::create(array(
            'string' => 'String',
            'stringBuilder' => sb()->add('StringBuilder'),
            'null' => null,
            'empty' => '',
            'zero' => 0,
            'int' => 45,
            'float' => 42.5,
            'bool' => true
        ));

        $this->assertEquals('String', $attribs->getAttribute('string'));
        $this->assertEquals('StringBuilder', $attribs->getAttribute('stringBuilder'));
        $this->assertEquals('', $attribs->getAttribute('null'));
        $this->assertEquals('', $attribs->getAttribute('empty'));
        $this->assertEquals('0', $attribs->getAttribute('zero'));
        $this->assertEquals('45', $attribs->getAttribute('int'));
        $this->assertEquals('42.5', $attribs->getAttribute('float'));
        $this->assertEquals('true', $attribs->getAttribute('bool'));
    }
}
