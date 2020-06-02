<?php
/**
 * File containing the {@see XMLHelper_SimpleXML} class.
 * 
 * @package AppUtils
 * @subpackage XMLHelper
 * @see XMLHelper_SimpleXML
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Utility class to create SimpleXML elements, with
 * easier error handling. 
 * 
 * @package AppUtils
 * @subpackage XMLHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class XMLHelper_SimpleXML
{
    const ERROR_NOT_LOADED_YET = 56501;
    
   /**
    * @var \SimpleXMLElement|NULL
    */
    private $element = null;
    
   /**
    * @var XMLHelper_SimpleXML_Error[]
    */
    private $errors = array();
    
   /**
    * Creates a simplexml instance from an XML string.
    *
    * NOTE: returns false in case of a fatal error.
    *
    * @param string $string
    * @return \SimpleXMLElement|NULL
    */
    public function loadString(string $string) : ?\SimpleXMLElement
    {
        return $this->load('string', $string);
    }
    
   /**
    * Creates a simplexml instance from an XML file.
    * 
    * NOTE: returns false in case of a fatal error.
    * 
    * @param string $file
    * @return \SimpleXMLElement|NULL
    */
    public function loadFile(string $file) : ?\SimpleXMLElement
    {
        return $this->load('file', $file);
    }
    
    private function load(string $mode, string $subject) : ?\SimpleXMLElement
    { 
        $this->errors = array();
        
        // to be able to fetch errors, we have to 
        // enable the internal errors.
        $use_errors = libxml_use_internal_errors(true);
        
        $this->element = $this->createInstance($mode, $subject);
        
        $this->detectErrors();
        
        // restore the previous setting just in case
        libxml_use_internal_errors($use_errors);
        
        return $this->element;
    }
    
    private function detectErrors() : void
    {
        // add any errors that were triggered, using the
        // error wrappers.
        $errors = libxml_get_errors();
        
        foreach($errors as $error) 
        {
            $this->errors[] = new XMLHelper_SimpleXML_Error($this, $error);
        }
        
        libxml_clear_errors();
    }
    
    private function createInstance(string $mode, string $subject) : ?\SimpleXMLElement
    {
        $function = 'simplexml_load_'.$mode;
        
        $xml = $function($subject);
        
        if($xml instanceof \SimpleXMLElement)
        {
            return $xml;
        }
        
        return null;
    }
    
    public function getConverter() : XMLHelper_Converter
    {
        if($this->element instanceof \SimpleXMLElement)
        {
            return XMLHelper::convertElement($this->element);
        }
        
        throw $this->createNotLoadedException(); 
    }
     
    public function toArray() : array
    {
        return $this->getConverter()->toArray();
    }
    
    public function toJSON() : string
    {
        return $this->getConverter()->toJSON();
    }
    
    public function dispose() : void
    {
        $this->element = null;
        $this->errors = array();
    }
    
    public function hasErrors() : bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Retrieves all errors (if any) recorded during parsing.
     * @return XMLHelper_SimpleXML_Error[]
     */
    public function getErrorMessages()
    {
        return $this->errors;
    }
    
    private function createNotLoadedException() : XMLHelper_Exception
    {
        return new XMLHelper_Exception(
            'No SimpleXML element loaded.',
            'No element has been loaded yet. The loadFile() or loadString() method must be called first.',
            self::ERROR_NOT_LOADED_YET
        );
    }
}
