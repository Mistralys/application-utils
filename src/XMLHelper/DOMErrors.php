<?php
/**
 * File containing the {@see AppUtils\XMLHelper_DOMErrors} class.
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper_DOMErrors
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Container for libxml errors: converts an array of libxml errors
 * to dom error instances which are a lot easier to work with.
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class XMLHelper_DOMErrors
{
    public const SERIALIZE_SEPARATOR = '__SERSEP__';
    
   /**
    * @var XMLHelper_DOMErrors_Error[]
    */
    private $errors;
    
   /**
    * @param \LibXMLError[]|XMLHelper_DOMErrors_Error[] $libxmlErrors
    */
    public function __construct(array $libxmlErrors)
    {
        foreach($libxmlErrors as $error)
        {
            if($error instanceof XMLHelper_DOMErrors_Error)
            {
                $this->errors[] = $error;
            }
            else if($error instanceof \LibXMLError)
            {
                $this->errors[] = new XMLHelper_DOMErrors_Error($error);
            }
        }
    }
    
    public function isValid() : bool
    {
        return empty($this->errors);
    }
    
    public function getAll()
    {
        return $this->errors;
    }
    
   /**
    * Retrieves all warnings, if any.
    * 
    * @return XMLHelper_DOMErrors_Error[]
    */
    public function getWarnings()
    {
        return $this->getByLevel(LIBXML_ERR_WARNING);
    }
    
    public function getErrors()
    {
        return $this->getByLevel(LIBXML_ERR_ERROR);
    }
    
    public function getFatalErrors()
    {
        return $this->getByLevel(LIBXML_ERR_FATAL);
    }
    
    public function getNestingErrors()
    {
        return $this->getByCode(XMLHelper_LibXML::TAG_NAME_MISMATCH);
    }
    
    public function hasWarnings() : bool
    {
        return $this->hasErrorsByLevel(LIBXML_ERR_WARNING);
    }
    
    public function hasErrors() : bool
    {
        return $this->hasErrorsByLevel(LIBXML_ERR_ERROR);
    }
    
    public function hasFatalErrors() : bool
    {
        return $this->hasErrorsByLevel(LIBXML_ERR_FATAL);
    }
    
    public function hasNestingErrors() : bool
    {
        return $this->hasErrorsByCode(XMLHelper_LibXML::TAG_NAME_MISMATCH);
    }
    
    public function hasUnknownTags() : bool
    {
        return $this->hasErrorsByCode(XMLHelper_LibXML::XML_HTML_UNKNOWN_TAG);
    }
    
    
   /**
    * Retrieves all errors by the specified libxml error level.
    * 
    * @param int $level
    * @return \AppUtils\XMLHelper_DOMErrors_Error[]
    */
    public function getByLevel(int $level)
    {
        $result = array();
        
        foreach($this->errors as $error)
        {
            if($error->isLevel($level))
            {
                $result[] = $error;
            }
        }
        
        return $result;
    }
    
   /**
    * Retrieves all errors by the specified libxml error code.
    * 
    * @param int $code
    * @return \AppUtils\XMLHelper_DOMErrors_Error[]
    */
    public function getByCode(int $code)
    {
        $result = array();
        
        foreach($this->errors as $error)
        {
            if($error->isCode($code))
            {
                $result[] = $error;
            }
        }
        
        return $result;
    }
    
   /**
    * Checks whether there are errors matching the libxml error level.
    * 
    * @param int $level
    * @return bool
    */
    public function hasErrorsByLevel(int $level) : bool
    {
        foreach($this->errors as $error)
        {
            if($error->isLevel($level))
            {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * Checks whether there are any errors matching the libxml error code.
    * 
    * @param int $code
    * @return bool
    */
    public function hasErrorsByCode(int $code) : bool
    {
        foreach($this->errors as $error)
        {
            if($error->isCode($code))
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function toArray() : array
    {
        $result = array();
        
        foreach($this->errors as $error)
        {
            $result[] = $error->toArray();
        }
        
        return $result;
    }
    
   /**
    * Serializes the errors collection, so it can be stored and
    * restored as needed, using the `fromSerialized()` method.
    * 
    * @return string
    * @see XMLHelper_DOMErrors::fromSerialized()
    */
    public function serialize() : string
    {
        $data = array();
        
        foreach($this->errors as $error)
        {
            $data[] = $error->serialize();
        }
        
        return implode(self::SERIALIZE_SEPARATOR, $data);
    }
    
   /**
    * Restores the errors collection from a previously serialized
    * collection, using `serialize()`. 
    * 
    * @param string $serialized
    * @return XMLHelper_DOMErrors
    * @see XMLHelper_DOMErrors::serialize()
    */
    public static function fromSerialized(string $serialized) : XMLHelper_DOMErrors
    {
        $parts = explode(self::SERIALIZE_SEPARATOR, $serialized);
        $list = array();
        
        foreach($parts as $part)
        {
            $list[] = XMLHelper_DOMErrors_Error::fromSerialized($part);
        }
        
        return new XMLHelper_DOMErrors($list);
    }
}
