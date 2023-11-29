<?php

declare(strict_types=1);

namespace AppUtilsTests\XMLHelper;

use PHPUnit\Framework\TestCase;
use AppUtils\XMLHelper_HTMLLoader;
use AppUtils\XMLHelper_Exception;
use AppUtils\XMLHelper;

final class HTMLLoaderTests extends TestCase
{
    public function test_createFragment() : void
    {
        $loader = XMLHelper_HTMLLoader::loadFragment('<p>Text</p>');

        $nodes = $loader->getFragmentNodes();

        $this->assertTrue($loader->getErrors()->isValid());
        $this->assertCount(1, $nodes);
        $this->assertEquals('<p>Text</p>', $loader->fragmentToXML());
    }

    /**
     * Ensure that the XMLHelper's {@see XMLHelper::string2xml()}
     * method corresponds to the expected output of the fragment
     * loader's {@see XMLHelper_HTMLLoader::fragmentToXML()} method.
     */
    public function test_string2xml() : void
    {
        $fragment = '<p>Text</p>';
        $expected = '<p>Text</p>';

        $loader = XMLHelper_HTMLLoader::loadFragment($fragment);

        $this->assertEquals($expected, $loader->fragmentToXML());
        $this->assertEquals($expected, XMLHelper::string2xml($fragment));
    }

    public function test_fragmentCheckBody() : void
    {
        $this->expectException(XMLHelper_Exception::class);

        XMLHelper_HTMLLoader::loadFragment('<body><p>Text</p></body>');
    }

    public function test_fragmentCheckDoctype() : void
    {
        $this->expectException(XMLHelper_Exception::class);

        XMLHelper_HTMLLoader::loadFragment('<!doctype><p>Text</p>');
    }

    public function test_createDocument() : void
    {
        $html = '<!doctype html><html lang="en"><body><p>Text</p></body></html>';
        $expected = '<p>Text</p>';

        $loader = XMLHelper_HTMLLoader::loadHTML($html);

        $nodes = $loader->getFragmentNodes();

        $this->assertTrue($loader->getErrors()->isValid());
        $this->assertCount(1, $nodes);
        $this->assertEquals($expected, $loader->fragmentToXML());
    }
}
