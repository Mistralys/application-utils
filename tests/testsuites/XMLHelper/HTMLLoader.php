<?php

use PHPUnit\Framework\TestCase;
use AppUtils\XMLHelper_HTMLLoader;
use AppUtils\XMLHelper_Exception;
use AppUtils\XMLHelper;

final class XMLHelper_HTMLLoaderTest extends TestCase
{
    public function test_createFragment()
    {
        $loader = XMLHelper_HTMLLoader::loadFragment('<p>Text</p>');
        
        $nodes = $loader->getFragmentNodes();
        
        $this->assertTrue($loader->getErrors()->isValid());
        $this->assertEquals(1, count($nodes));
        $this->assertEquals('<p>Text</p>', $loader->fragmentToXML());
    }
    
   /**
    * Ensure that the XMLHelper's string2xml method corresponds
    * to the expected output of the fragment loader's fragmentToXML().
    */
    public function test_string2xml()
    {
        $fragment = '<p>Text</p>';
        $expected = '<p>Text</p>';
        
        $loader = XMLHelper_HTMLLoader::loadFragment($fragment);
        
        $this->assertEquals($expected, $loader->fragmentToXML());
        $this->assertEquals($expected, XMLHelper::string2xml($fragment));
    }
    
    public function test_fragmentCheckBody()
    {
        $this->expectException(XMLHelper_Exception::class);
        
        XMLHelper_HTMLLoader::loadFragment('<body><p>Text</p></body>');
    }

    public function test_fragmentCheckDoctype()
    {
        $this->expectException(XMLHelper_Exception::class);
        
        XMLHelper_HTMLLoader::loadFragment('<!doctype><p>Text</p>');
    }
    
    public function test_createDocument()
    {
        $html = '<!doctype html><html><body><p>Text</p></body></html>';
        $expected = '<p>Text</p>';
        
        $loader = XMLHelper_HTMLLoader::loadHTML($html);
        
        $nodes = $loader->getFragmentNodes();
        
        $this->assertTrue($loader->getErrors()->isValid());
        $this->assertEquals(1, count($nodes));
        $this->assertEquals($expected, $loader->fragmentToXML());
    }
}
