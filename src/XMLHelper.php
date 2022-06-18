<?php
/**
 * File containing the {@see AppUtils\XMLHelper} class.
 * 
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper
 */

namespace AppUtils;

use DOMDocument;
use DOMNode;
use DOMElement;
use SimpleXMLElement;

/**
 * Simple XML utility class that makes it easier to work
 * with the native PHP DOMDocument class. Simplifies the
 * code required to add common elements without going all
 * the way of being an abstraction layer.
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class XMLHelper
{
    public const ERROR_CANNOT_APPEND_FRAGMENT = 491001; 

   /**
    * @var boolean
    */
    private static $simulation = false;

    /**
     * @var DOMDocument
     */
    private $dom;
    
   /**
    * Creates a new XMLHelper instance.
    * 
    * @return XMLHelper
    */
    public static function create() : XMLHelper
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        return new XMLHelper($dom);
    }
    
   /**
    * Creates a converter instance from an XML file.
    * @param string $xmlFile
    * @return XMLHelper_Converter
    */
    public static function convertFile(string $xmlFile) : XMLHelper_Converter
    {
        return XMLHelper_Converter::fromFile($xmlFile);
    }
    
   /**
    * Creates a converter from an XML string.
    * @param string $xmlString
    * @return XMLHelper_Converter
    */
    public static function convertString(string $xmlString) : XMLHelper_Converter
    {
        return XMLHelper_Converter::fromString($xmlString);
    }

   /**
    * Creates a converter from a SimpleXMLElement instance.
    * @param SimpleXMLElement $element
    * @return XMLHelper_Converter
    */
    public static function convertElement(SimpleXMLElement $element) : XMLHelper_Converter
    {
        return XMLHelper_Converter::fromElement($element);
    }
   
   /**
    * Creates a converter from a DOMElement instance.
    * @param DOMElement $element
    * @return XMLHelper_Converter
    */
    public static function convertDOMElement(DOMElement $element) : XMLHelper_Converter
    {
        return XMLHelper_Converter::fromDOMElement($element);
    }

   /**
    * Creates a new helper using an existing DOMDocument object.
    * @param DOMDocument $dom
    */
    public function __construct(DOMDocument $dom)
    {
        $this->dom = $dom;
    }

   /**
    * @return DOMDocument
    */
    public function getDOM() : DOMDocument
    {
        return $this->dom;
    }

   /**
    * Adds an attribute to an existing tag with
    * the specified value.
    *
    * @param DOMNode $parent
    * @param string $name
    * @param mixed $value
    * @return DOMNode
    */
    public function addAttribute(DOMNode $parent, string $name, $value)
    {
        $node = $this->dom->createAttribute($name);
        $text = $this->dom->createTextNode(strval($value));
        $node->appendChild($text);

        return $parent->appendChild($node);
    }

   /**
    * Adds several attributes to the target node.
    * 
    * @param DOMNode $parent
    * @param array<string,mixed> $attributes
    */
    public function addAttributes(DOMNode $parent, array $attributes) : void
    {
        foreach ($attributes as $name => $value) {
            $this->addAttribute($parent, $name, $value);
        }
    }

   /**
    * Adds a tag without content.
    *
    * @param DOMNode $parent
    * @param string $name
    * @param integer $indent
    * @return DOMNode
    */
    public function addTag(DOMNode $parent, string $name, int $indent = 0) : DOMNode
    {
        if ($indent > 0) {
            $this->indent($parent, $indent);
        }

        return $parent->appendChild(
            $this->dom->createElement($name)
        );
    }

    public function removeTag(DOMElement $tag) : void
    {
        if(isset($tag->parentNode))
        {
            $tag->parentNode->removeChild($tag);
        }
    }
    
    public function indent(DOMNode $parent, int $amount) : void
    {
        $parent->appendChild($this->dom->createTextNode(str_repeat("\t", $amount)));
    }

   /**
    * Adds a tag with textual content, like:
    *
    * <tagname>text</tagname>
    *
    * @param DOMNode $parent
    * @param string $name
    * @param string $text
    * @param integer $indent
    * @return DOMNode
    */
    public function addTextTag(DOMNode $parent, string $name, string $text, int $indent = 0) : DOMNode
    {
        if ($indent > 0) {
            $this->indent($parent, $indent);
        }

        $tag = $this->dom->createElement($name);
        $text = $this->dom->createTextNode($text);
        $tag->appendChild($text);

        return $parent->appendChild($tag);
    }

   /**
    * Adds a tag with textual content, like:
    *
    * <tagname>text</tagname>
    *
    * and removes <p> tags
    *
    * @param DOMNode $parent
    * @param string $name
    * @param string $text
    * @param integer $indent
    * @return DOMNode
    */
    public function addEscapedTag(DOMNode $parent, string $name, string $text, int $indent = 0)
    {
        if ($indent > 0) {
            $this->indent($parent, $indent);
        }

        $text = preg_replace('#<p>(.*)</p>#isUm', '$1', $text);

        $tag = $this->dom->createElement($name);
        $text = $this->dom->createTextNode($text);
        $tag->appendChild($text);

        return $parent->appendChild($tag);
    }

   /**
    * Adds a tag with HTML content, like:
    *
    * <tagname><i>text</i></tagname>
    *
    * Tags will not be escaped.
    *
    * @param DOMNode $parent
    * @param string $name
    * @param string $text
    * @param integer $indent
    * @return DOMNode
    */
    public function addFragmentTag(DOMNode $parent, string $name, string $text, int $indent = 0)
    {
        if ($indent > 0) {
            $this->indent($parent, $indent);
        }

        $tag = $this->dom->createElement($name);

        if (!empty($text)) {
            $fragment = $this->dom->createDocumentFragment();
            if(!@$fragment->appendXML($text)) {
                throw new XMLHelper_Exception(
                    'Cannot append XML fragment',
                    sprintf(
                        'Appending text content to the fragment tag [%s] failed. Text content: [%s].',
                        $name,
                        htmlspecialchars($text, ENT_QUOTES, 'UTF-8')    
                    ),
                    self::ERROR_CANNOT_APPEND_FRAGMENT
                );
            }
            $tag->appendChild($fragment);
        }

        return $parent->appendChild($tag);
    }

   /**
    * Adds a tag with CDATA content, like:
    *
    * <tagname><![CDATA[value]]></tagname>
    *
    * @param DOMNode $parent
    * @param string $name
    * @param string $content
    * @return DOMNode
    */
    public function addCDATATag(DOMNode $parent, string $name, string $content) : DOMNode
    {
        $tag = $this->dom->createElement($name);
        $text = $this->dom->createCDATASection($content);
        $tag->appendChild($text);

        return $parent->appendChild($tag);
    }

   /**
    * Creates the root element of the document.
    * @param string $name
    * @param array<string,mixed> $attributes
    * @return DOMNode
    */
    public function createRoot(string $name, array $attributes=array())
    {
        $root = $this->dom->appendChild($this->dom->createElement($name));
        $this->addAttributes($root, $attributes);
        return $root;
    }

   /**
    * Escaped the string for use in XML.
    * 
    * @param string $string
    * @return string
    */
    public function escape(string $string) : string
    {
        $string = preg_replace('#<p>(.*)</p>#isUm', '$1', $string);

        return $string;
    }

    public function escapeText(string $string) : string 
    {
        $string = str_replace('&amp;', 'AMPERSAND_ESCAPE', $string);
        $string = str_replace('&lt;', 'LT_ESCAPE', $string);
        $string = str_replace('&gt;', 'GT_ESCAPE', $string);

        $string = str_replace('&nbsp;',' ',  $string);
        $string = str_replace('&','&amp;',  $string);

        return $string;
    }

   /**
    * Sends the specified XML string to the browser with
    * the correct headers to trigger a download of the XML
    * to a local file.
    * 
    * NOTE: Ensure calling exit after this is done, and to
    * not send additional content, which would corrupt the 
    * download.
    *
    * @param string $xml
    * @param string $filename
    */
    public static function downloadXML(string $xml, string $filename = 'download.xml') : void
    {
        if(!headers_sent() && !self::$simulation) 
        {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        
        echo $xml;
    }

   /**
    * Sends the specified XML string to the browser with
    * the correct headers and terminates the request.
    *
    * @param string $xml
    */
    public static function displayXML(string $xml) : void
    {
        if(!headers_sent() && !self::$simulation) 
        {
            header('Content-Type:text/xml; charset=utf-8');
        }
        
        if(self::$simulation) 
        {
            $xml = '<pre>'.htmlspecialchars($xml).'</pre>';
        }
        
        echo $xml;
    }

    /**
     * Shorthand method for building error xml and sending it
     * to the browser.
     *
     * @param string|number $code
     * @param string $message
     * @param string $title
     * @param array<string,string> $customInfo Associative array with name => value pairs for custom tags to add to the output xml
     * @see buildErrorXML()
     */
    public static function displayErrorXML($code, string $message, string $title, array $customInfo=array())
    {
        if(!headers_sent() && !self::$simulation) {
            header('HTTP/1.1 400 Bad Request: ' . $title, true, 400);
        }

        self::displayXML(self::buildErrorXML($code, $message, $title, $customInfo));
    }
    
    public static function setSimulation(bool $simulate=true) : void
    {
        self::$simulation = $simulate;
    }

    /**
     * Creates XML markup to describe an application success
     * message when using XML-based services. Creates XML
     * with the following structure:
     *
     * <success>
     *     <message>Success message here</message>
     *     <time>YYYY-MM-DD HH:II:SS</time>
     * </success>
     *
     * @param string $message
     * @return string
     */
    public static function buildSuccessXML(string $message) : string
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $helper = new XMLHelper($xml);

        $root = $helper->createRoot('success');
        $helper->addTextTag($root, 'message', $message);
        $helper->addTextTag($root, 'time', date('Y-m-d H:i:s'));

        return $xml->saveXML();
    }

   /**
    * Creates XML markup to describe an application error
    * when using XML services. Creates XML with the
    * following structure:
    *
    * <error>
    *     <id>99</id>
    *     <message>Full error message text</message>
    *     <title>Short error label</title>
    * </error>
    *
    * @param string|number $code
    * @param string $message
    * @param string $title
    * @param array<string,string> $customInfo
    * @return string
    */
    public static function buildErrorXML($code, string $message, string $title, array $customInfo=array())
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $helper = new XMLHelper($xml);

        $root = $helper->createRoot('error');
        
        $helper->addTextTag($root, 'id', $code);
        $helper->addTextTag($root, 'message', $message);
        $helper->addTextTag($root, 'title', $title);
        $helper->addTextTag($root, 'request_uri', $_SERVER['REQUEST_URI']);
        
        foreach($customInfo as $name => $value) {
            $helper->addTextTag($root, $name, $value);
        }

        return $xml->saveXML();
    }

    public function appendNewline(DOMNode $node) : void
    {
        $nl = $this->dom->createTextNode("\n");
        $node->appendChild($nl);
    }

    public function saveXML() : string
    {
        return $this->dom->saveXML();
    }
    
   /**
    * Creates a new SimpleXML helper instance: this
    * object is useful to work with loading XML strings
    * and files with easy access to any errors that 
    * may occurr, since the simplexml functions can be
    * somewhat cryptic.
    * 
    * @return XMLHelper_SimpleXML
    */
    public static function createSimplexml() : XMLHelper_SimpleXML
    {
        return new XMLHelper_SimpleXML();
    }
    
   /**
    * Converts a string to valid XML: can be a text only string
    * or an HTML string. Returns valid XML code.
    * 
    * NOTE: The string may contain custom tags, which are 
    * preserved.
    * 
    * @param string $string
    * @throws XMLHelper_Exception
    * @return string
    */
    public static function string2xml(string $string) : string
    {
        return XMLHelper_HTMLLoader::loadFragment($string)->fragmentToXML();
    }
}
