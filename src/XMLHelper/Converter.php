<?php
/**
 * File containing the {@link XMLHelper_Converter} class.
 * 
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper_Converter
 */

namespace AppUtils;

/**
 * Simple XML converter that can transform an XML document
 * to JSON or an associative array.
 * 
 * It uses a custom JSON decorator to convert a SimpleXMLElement,
 * which solves many of the usual issues when trying to convert
 * those to JSON. It is not a catch-all, but works well with
 * most XML documents.
 * 
 * Converting an XML file to array:
 * 
 * <pre>
 * $converter = XMLHelper_Converter::fromFile('path/to/xmlfile.xml');
 * $array = $converter->toArray();
 * </pre>
 * 
 * Converting an XML string to array:
 * 
 * <pre>
 * $converter = XMLHelper_Converter::fromString('<document>XML source here</document>');
 * $array = $converter->toArray();
 * </pre>
 * 
 * @package Application Utils
 * @subpackage XMLHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 */
class XMLHelper_Converter
{
    const ERROR_FAILED_CONVERTING_TO_JSON = 37901;
    
   /**
    * @var \SimpleXMLElement
    */
    protected $xml;
    
   /**
    * @var string
    */
    protected $json;
    
    protected function __construct(\SimpleXMLElement $element)
    {
        $this->xml = $element;
    }
    
   /**
    * Factory method: creates a converter from an XML file on disk.
    * 
    * @param string $path
    * @return \AppUtils\XMLHelper_Converter
    */
    public static function fromFile(string $path)
    {
        $xmlString = file_get_contents($path);
        return self::fromString($xmlString);
    }
 
   /**
    * Factory method: creates a converter from an XML string.
    * 
    * @param string $xmlString
    * @return \AppUtils\XMLHelper_Converter
    */
    public static function fromString(string $xmlString)
    {
        $element = new \SimpleXMLElement($xmlString);
        
        return self::fromElement($element);
    }
    
   /**
    * Factory method: creates a converter from an existing SimpleXMLElement instance.
    * 
    * @param \SimpleXMLElement $element
    * @return \AppUtils\XMLHelper_Converter
    */
    public static function fromElement(\SimpleXMLElement $element)
    {
        $obj = new XMLHelper_Converter($element);
        return $obj;
    }
    
   /**
    * Converts the XML to JSON.
    * 
    * @throws XMLHelper_Exception
    * @return string
    */
    public function toJSON() : string
    {
        if(isset($this->json)) {
            return $this->json;
        }
        
        $decorator = new XMLHelper_Converter_Decorator($this->xml);
        
        $this->json = json_encode($decorator, JSON_PRETTY_PRINT);
        
        unset($this->xml);
        
        if($this->json !== false) {
            return $this->json;
        }
        
        throw new XMLHelper_Exception(
            'Could not convert the XML source to JSON',
            sprintf(
                'Native error: #%s %s',
                json_last_error(),
                json_last_error_msg()
            ),
            self::ERROR_FAILED_CONVERTING_TO_JSON
        );
    }
    
   /**
    * Converts the XML to an associative array.
    * @return array
    * @throws XMLHelper_Exception
    */
    public function toArray() : array 
    {
        $json = $this->toJSON();
        
        return json_decode($json, true);
    }
}
