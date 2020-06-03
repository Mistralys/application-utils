<?php
/**
 * File containing the {@see AppUtils\XMLHelper_StringLoader} class.
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper_StringLoader
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Wrapper around the `DOMDocument->loadHTML()` method to
 * make it easier to work with, and add a number of utility
 * methods around it. 
 * 
 * Usage:
 * 
 * <code>
 * <?php
 * // Create a loader from a full HTML document string
 * $loader = XMLHelper_HTMLLoader::loadHTML($htmlDocument);
 * 
 * // Create a loader from an HTML fragment
 * $loader = XMLHelper_HTMLLoader::loadHTMLFragment('<p>Fragment</p>');
 * ?>
 * </code>
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class XMLHelper_HTMLLoader
{
    const ERROR_STRING_ALREADY_HAS_BODY_TAG = 57001;
    
   /**
    * @var \DOMElement
    */
    private $bodyNode;
    
   /**
    * @var XMLHelper_DOMErrors
    */
    private $errors;
    
   /**
    * @var string
    */
    private static $htmlTemplate = 
    '<!DOCTYPE html>'.
    '<html>'.
        '<head>'.
            '<meta charset="utf-8">'.
        '</head>'.
        '<body>'.
            '%1$s'.
        '</body>'.
    '</html>';
    
   /**
    * @var \DOMDocument
    */
    private $dom;

    private function __construct(string $html)
    {
        $this->load($html);
    }
    
   /**
    * Creates an HTML loader from an HTML fragment (without
    * doctype, head and body elements).
    * 
    * @param string $fragment
    * @return XMLHelper_HTMLLoader
    */
    public static function loadFragment(string $fragment) : XMLHelper_HTMLLoader
    {
        self::checkFragment($fragment);
        
        // inject the HTML fragment into a valid HTML structure
        $pseudoHTML = sprintf(self::$htmlTemplate, $fragment);
        
        return new XMLHelper_HTMLLoader($pseudoHTML);
    }
    
   /**
    * Creates an HTML loader from a full HTML document (including
    * doctype, head and body elements).
    * 
    * @param string $html
    * @return XMLHelper_HTMLLoader
    */
    public static function loadHTML(string $html) : XMLHelper_HTMLLoader
    {
        return  new XMLHelper_HTMLLoader($html);
    }

   /**
    * Verifies that the fragment does not already contain a body element or doctype.
    * 
    * @param string $fragment
    * @throws XMLHelper_Exception
    */
    private static function checkFragment(string $fragment) : void
    {
        if(!stristr($fragment, '<body') && !stristr($fragment, 'doctype'))
        {
            return;
        }
        
        throw new XMLHelper_Exception(
            'Cannot convert string with existing body or doctype',
            sprintf(
                'The string already contains a body tag or doctype, which conflicts with the conversion process. Source string: [%s]',
                htmlspecialchars($fragment)
            ),
            self::ERROR_STRING_ALREADY_HAS_BODY_TAG
        );
    }
        
    private function load(string $html) : void
    {
        $prev = libxml_use_internal_errors(true);
                
        $this->dom = new \DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        $this->dom->loadHTML($html);
        
        $this->errors = new XMLHelper_DOMErrors(libxml_get_errors());
        
        libxml_use_internal_errors($prev);
        
        $this->bodyNode = $this->dom->getElementsByTagName('body')->item(0);
    }
    
    public function getBodyNode() : \DOMElement
    {
        return $this->bodyNode;
    }
    
   /**
    * Retrieves the document's `<body>` tag node.
    * 
    * @return \DOMDocument
    */
    public function getDOM() : \DOMDocument
    {
        return $this->dom;
    }
    
   /**
    * Retrieves all nodes from the HTML fragment (= child nodes
    * of the `<body>` element).
    * 
    * @return \DOMNodeList
    */
    public function getFragmentNodes() : \DOMNodeList
    {
        return $this->bodyNode->childNodes;
    }
    
   /**
    * Retrieves the LibXML HTML parsing errors collection, which
    * can be used to review any errors that occurred while loading
    * the HTML document.
    * 
    * @return XMLHelper_DOMErrors
    */
    public function getErrors() : XMLHelper_DOMErrors
    {
        return $this->errors;
    }
    
   /**
    * Returns a valid HTML string.
    * 
    * @return string
    */
    public function toHTML() : string
    {
        return $this->dom->saveHTML();
    }
    
   /**
    * Returns a valid XML string.
    * 
    * @return string
    */
    public function toXML() : string
    {
        return $this->dom->saveXML();
    }
    
   /**
    * Converts the HTML fragment to valid XML (= all
    * child nodes of the `<body>` element).
    * 
    * @return string
    */
    public function fragmentToXML() : string
    {
        $nodes = $this->getFragmentNodes();
        
        // capture all elements except the body tag itself
        $xml = '';
        foreach($nodes as $child) 
        {
            $xml .= $this->dom->saveXML($child);
        }
        
        return $xml;
    }
}
