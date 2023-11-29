<?php

declare(strict_types=1);

namespace AppUtilsTests\XMLHelper;

use PHPUnit\Framework\TestCase;

use AppUtils\XMLHelper;
use AppUtils\XMLHelper_SimpleXML;
use AppUtils\XMLHelper_Exception;

final class SimpleXMLTests extends TestCase
{
    public function test_create() : void
    {
        $simple = XMLHelper::createSimplexml();

        $this->assertInstanceOf(XMLHelper_SimpleXML::class, $simple);
    }

    public function test_loadString() : void
    {
        $xmlString =
            '<?xml version="1.0" encoding="UTF-8"?>
<root type="root">
    <title>Title</title>
</root>';

        $simple = XMLHelper::createSimplexml();
        $simple->loadString($xmlString);

        $this->assertFalse($simple->hasErrors());

        $array = $simple->toArray();
        $this->assertArrayHasKey('title', $array);

    }

    public function test_loadError() : void
    {
        $xmlString =
            '<?xml version="1.0" encoding="UTF-8"?>
<root type="root">
    <title>Title</notTitle>
</root>';

        $simple = XMLHelper::createSimplexml();
        $simple->loadString($xmlString);

        $this->assertTrue($simple->hasErrors());
    }

    /**
     * Check that the parsing does not modify the
     * internal errors setting: it should not be
     * true after loading the XML source.
     */
    public function test_restoreLibxmlErrorSettings() : void
    {
        libxml_use_internal_errors(false);

        $xmlString =
            '<?xml version="1.0" encoding="UTF-8"?>
<root type="root">
    <title>Title</title>
</root>';

        $simple = XMLHelper::createSimplexml();
        $simple->loadString($xmlString);

        $setting = libxml_use_internal_errors(false);

        $this->assertFalse($setting);
    }

    public function test_getErrorsBeforeLoad() : void
    {
        $simple = XMLHelper::createSimplexml();

        $this->expectException(XMLHelper_Exception::class);

        $simple->toArray();
    }
}
