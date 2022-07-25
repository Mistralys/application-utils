<?php

declare(strict_types=1);

namespace AppUtils;

use LibXMLError;

class XMLHelper_SimpleXML_Error
{
    protected XMLHelper_SimpleXML $xml;
    
   /**
    * @var  LibXMLError
    */
    protected LibXMLError $nativeError;
    
    public function __construct(XMLHelper_SimpleXML $xml, LibXMLError $nativeError)
    {
        $this->xml = $xml;
        $this->nativeError = $nativeError;
    }
    
    public function getLevel() : int
    {
        return (int)$this->nativeError->level;
    }
    
    public function isWarning() : bool
    {
        return $this->getLevel() === LIBXML_ERR_WARNING;
    }
    
    public function isFatal() : bool
    {
        return $this->getLevel() === LIBXML_ERR_FATAL;
    }
    
    public function isError() : bool
    {
        return $this->getLevel() === LIBXML_ERR_ERROR;
    }
    
    public function getCode() : int
    {
        return (int)$this->nativeError->code;
    }
    
    public function getMessage() : string
    {
        return htmlspecialchars($this->nativeError->message);
    }
}
