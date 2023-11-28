<?php

use PHPUnit\Framework\TestCase;

use AppUtils\XMLHelper;
use AppUtils\XMLHelper_SimpleXML;
use AppUtils\XMLHelper_Exception;

final class XMLHelper_SimpleXMLTest extends TestCase
{
    public function test_create()
    {
        $simple = XMLHelper::createSimplexml();
        
        $this->assertInstanceOf(XMLHelper_SimpleXML::class, $simple);
    }

    public function test_loadString()
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
    
    public function test_loadError()
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
    public function test_restoreLibxmlErrorSettings()
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
    
    public function test_getErrorsBeforeLoad()
    {
        $simple = XMLHelper::createSimplexml();
        
        $this->expectException(XMLHelper_Exception::class);
        
        $simple->toArray();
    }
}
