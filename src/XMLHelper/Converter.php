<?php
/**
 * File containing the {@link XMLHelper_Converter} class.
 * 
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper_Converter
 */

namespace AppUtils;

use DOMElement;
use Exception;
use JsonException;
use SimpleXMLElement;

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
 * $converter = XMLHelper_Converter::fromFile('path/to/xml/file.xml');
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
    public const ERROR_FAILED_CONVERTING_TO_JSON = 37901;
    public const ERROR_CANNOT_CREATE_ELEMENT_FROM_STRING = 37902;
    
    protected SimpleXMLElement $xml;
    
   /**
    * @var string|NULL
    */
    protected ?string $json;
    
    protected function __construct(SimpleXMLElement $element)
    {
        $this->xml = $element;
    }

    /**
     * Factory method: creates a converter from an XML file on disk.
     *
     * @param string $path
     * @return XMLHelper_Converter
     * @throws FileHelper_Exception
     * @throws XMLHelper_Exception
     */
    public static function fromFile(string $path) : XMLHelper_Converter
    {
        return self::fromString(FileHelper::readContents($path));
    }

    /**
     * Factory method: creates a converter from an XML string.
     *
     * @param string $xmlString
     * @return XMLHelper_Converter
     * @throws XMLHelper_Exception
     */
    public static function fromString(string $xmlString) : XMLHelper_Converter
    {
        try
        {
            return self::fromElement(new SimpleXMLElement($xmlString));
        }
        catch (Exception $e)
        {
            throw new XMLHelper_Exception(
                'Cannot create new element from string.',
                '',
                self::ERROR_CANNOT_CREATE_ELEMENT_FROM_STRING,
                $e
            );
        }
    }
    
   /**
    * Factory method: creates a converter from an existing SimpleXMLElement instance.
    * 
    * @param SimpleXMLElement $element
    * @return XMLHelper_Converter
    */
    public static function fromElement(SimpleXMLElement $element) : XMLHelper_Converter
    {
        return new XMLHelper_Converter($element);
    }

   /**
    * Factory method: creates a converter from an existing SimpleXMLElement instance.
    *
    * @param DOMElement $element
    * @return XMLHelper_Converter
    */
    public static function fromDOMElement(DOMElement $element) : XMLHelper_Converter
    {
        return new XMLHelper_Converter(simplexml_import_dom($element));
    }
    
   /**
    * Converts the XML to JSON.
    * 
    * @return string
    * @throws XMLHelper_Exception|JsonException
    */
    public function toJSON() : string
    {
        if (isset($this->json))
        {
            return $this->json;
        }

        $decorator = new XMLHelper_Converter_Decorator($this->xml);

        try
        {
            $this->json = json_encode($decorator, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

            unset($this->xml);

            return $this->json;
        }
        catch (Exception $e)
        {
            throw new XMLHelper_Exception(
                'Could not convert the XML source to JSON',
                sprintf(
                    'Native error: #%s %s',
                    json_last_error(),
                    json_last_error_msg()
                ),
                self::ERROR_FAILED_CONVERTING_TO_JSON,
                $e
            );
        }
    }

   /**
    * Converts the XML to an associative array.
    * @return array<mixed>
    * @throws XMLHelper_Exception
    * @throws JsonException
    */
    public function toArray() : array 
    {
        $json = $this->toJSON();
        
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
