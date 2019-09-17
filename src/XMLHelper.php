<?php
/**
 * File containing the {@link XMLHelper} class.
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper
 */

namespace AppUtils;

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
    const ERROR_CANNOT_APPEND_FRAGMENT = 491001; 
    
    const ERROR_PARENT_NOT_A_NODE = 491002;

    public static function create()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        return new XMLHelper($dom);
    }

    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * Creates a new helper using an existing DOMDocument object.
     * @param \DOMDocument $dom
     */
    public function __construct(\DOMDocument $dom)
    {
        $this->dom = $dom;
    }

    /**
     * @return \DOMDocument
     */
    public function getDOM()
    {
        return $this->dom;
    }

    /**
     * Adds an attribute to an existing tag with
     * the specified value.
     *
     * @param \DOMNode $parent
     * @param string $name
     * @param mixed $value
     * @return \DOMNode
     */
    function addAttribute($parent, $name, $value)
    {
        if(!$parent instanceof \DOMNode) {
            throw new XMLHelper_Exception(
                'The specified parent node is not a node instance.',
                sprintf('Tried adding attribute [%s].', $name),
                self::ERROR_PARENT_NOT_A_NODE
            );
        }
        
        $node = $this->dom->createAttribute($name);
        $text = $this->dom->createTextNode($value);
        $node->appendChild($text);

        return $parent->appendChild($node);
    }

    public function addAttributes($parent, $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->addAttribute($parent, $name, $value);
        }
    }

    /**
     * Adds a tag without content.
     *
     * @param \DOMNode $parent
     * @param string $name
     * @return \DOMNode
     */
    public function addTag($parent, $name, $indent = 0)
    {
        if ($indent > 0) {
            $this->indent($parent, $indent);
        }

        return $parent->appendChild(
            $this->dom->createElement($name)
        );
    }

    public function removeTag(\DOMElement $tag)
    {
        $tag->parentNode->removeChild($tag);
    }
    
    public function indent(\DOMNode $parent, $amount)
    {
        $parent->appendChild($this->dom->createTextNode(str_repeat("\t", $amount)));
    }

    /**
     * Adds a tag with textual content, like:
     *
     * <tagname>text</tagname>
     *
     * @param \DOMNode $parent
     * @param string $name
     * @param string $text
     * @return \DOMNode
     */
    function addTextTag($parent, $name, $text, $indent = 0)
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
     * @param \DOMNode $parent
     * @param string $name
     * @param string $text
     * @return \DOMNode
     */
    function addEscapedTag($parent, $name, $text, $indent = 0)
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
     * @param \DOMNode $parent
     * @param string $name
     * @param string $text
     * @return \DOMNode
     */
    function addFragmentTag($parent, $name, $text, $indent = 0)
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
     * @param \DOMNode $parent
     * @param string $name
     * @param string $content
     * @return \DOMNode
     */
    function addCDATATag($parent, $name, $content)
    {
        $tag = $this->dom->createElement($name);
        $text = $this->dom->createCDATASection($content);
        $tag->appendChild($text);

        return $parent->appendChild($tag);
    }

    /**
     * Creates the root element of the document.
     * @param string $name
     * @param array $attributes
     * @return \DOMNode
     */
    function createRoot($name, $attributes=array())
    {
        $root = $this->dom->appendChild($this->dom->createElement($name));
        $this->addAttributes($root, $attributes);
        return $root;
    }

    function escape($string)
    {

        $string = preg_replace('#<p>(.*)</p>#isUm', '$1', $string);

        return $string;
    }

    function escapeText($string) {
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
     * to a local file and terminates the request.
     *
     * @param string $xml
     * @param string $filename
     */
    public static function downloadXML($xml, $filename = 'download.xml')
    {
        if(!headers_sent() && !self::$simulation) {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        
        echo $xml;
        exit;
    }

    /**
     * Sends the specified XML string to the browser with
     * the correct headers and terminates the request.
     *
     * @param string $xml
     */
    public static function displayXML($xml)
    {
        if(!headers_sent() && !self::$simulation) {
            header('Content-Type:text/xml; charset=utf-8');
        }
        
        if(self::$simulation) {
            $xml = '<pre>'.htmlspecialchars($xml).'</pre>';
        }
        
        echo $xml;
        exit;
    }

    /**
     * Shorthand method for building error xml and sending it
     * to the browser.
     *
     * @param string $code
     * @param string $message
     * @param string $title
     * @param string[] $customInfo Associative array with name => value pairs for custom tags to add to the output xml
     * @see buildErrorXML()
     */
    public static function displayErrorXML($code, $message, $title, $customInfo=array())
    {
        if(!headers_sent() && !self::$simulation) {
            header('HTTP/1.1 400 Bad Request: ' . $title, true, 400);
        }

        self::displayXML(self::buildErrorXML($code, $message, $title, $customInfo));
    }
    
    protected static $simulation = false;
    
    public static function setSimulation($simulate=true)
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
    public static function buildSuccessXML($message)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
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
     *       <id>99</id>
     *     <message>Full error message text</message>
     *     <title>Short error label</title>
     * </error>
     *
     * @param mixed $code
     * @param string $message
     * @param string $title
     * @return string
     */
    public static function buildErrorXML($code, $message, $title, $customInfo=array())
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
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

    public function appendNewline(\DOMNode $node)
    {
        $nl = $this->dom->createTextNode("\n");
        $node->appendChild($nl);
    }

    public function saveXML()
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
    public static function createSimplexml()
    {
        return new XMLHelper_SimpleXML();
    }
}
