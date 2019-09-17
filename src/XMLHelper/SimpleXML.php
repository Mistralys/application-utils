<?php

class XMLHelper_SimpleXML
{
    /**
     * @var SimpleXMLElement
     */
    protected $xml;
    
    protected $errors = array();
    
   /**
    * Creates a simplexml instance from an XML string.
    *
    * NOTE: returns false in case of a fatal error.
    *
    * @param string $string
    * @return SimpleXMLElement|bool
    */
    public function loadString($string)
    {
        return $this->load('string', $string);
    }
    
   /**
    * Creates a simplexml instance from an XML file.
    * 
    * NOTE: returns false in case of a fatal error.
    * 
    * @param string $file
    * @return SimpleXMLElement|bool
    */
    public function loadFile($file)
    {
        return $this->load('file', $file);
    }
    
    protected function load($mode, $subject)
    { 
        $this->errors = array();
        
        $function = 'simplexml_load_'.$mode;
        
        // to be able to fetch errors, we have to 
        // enable the internal errors.
        $use_errors = libxml_use_internal_errors(true);
        
        $this->xml = $function($subject);
        
        // add any errors that were triggered, using the
        // error wrappers.
        $errors = libxml_get_errors();
        foreach($errors as $error) {
            $this->errors[] = new XMLHelper_SimpleXML_Error($this, $error);
        }
        
        libxml_clear_errors();
        
        // restore the previous setting just in case
        libxml_use_internal_errors($use_errors);
        
        return $this->xml;
    }
    
    public function toArray()
    {
        $json = json_encode($this->xml);
        return json_decode($json, true);
    }
    
    public function dispose()
    {
        $this->xml = null;
        $this->errors = array();
    }
    
    public function hasErrors()
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
}

class XMLHelper_SimpleXML_Error
{
    protected $xml;
    
    protected $nativeError;
    
    public function __construct(XMLHelper_SimpleXML $xml, LibXMLError $nativeError)
    {
        $this->xml = $xml;
        $this->nativeError = $nativeError;
    }
    
    public function getLevel()
    {
        return $this->nativeError->level;
    }
    
    public function isWarning()
    {
        return $this->getLevel() == LIBXML_ERR_WARNING;
    }
    
    public function isFatal()
    {
        return $this->getLevel() == LIBXML_ERR_FATAL;
    }
    
    public function isError()
    {
        return $this->getLevel() == LIBXML_ERR_ERROR;
    }
    
    public function getCode()
    {
        return $this->nativeError->code;
    }
    
    public function getMessage()
    {
        return htmlspecialchars($this->nativeError->message);
    }
}
