<?php

namespace AppUtils;

class XMLHelper_SimpleXML_Error
{
    protected $xml;
    
   /**
    * @var  \LibXMLError
    */
    protected $nativeError;
    
    public function __construct(XMLHelper_SimpleXML $xml, \LibXMLError $nativeError)
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
