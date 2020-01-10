<?php
/**
 * File containing the {@link RequestHelper_Boundaries_Boundary} class.
 * @package Application Utils
 * @subpackage RequestHelper
 * @see RequestHelper_Boundaries_Boundary
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Handles the rendering of a single boundary.
 *
 * @package Application Utils
 * @subpackage RequestHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestHelper_Boundaries_Boundary
{
   /**
    * @var string
    */
    protected $content;
    
   /**
    * @var array
    */
    protected $dispositionParams = array();

   /**
    * @var string
    */
    protected $contentType = '';

   /**
    * @var string
    */
    protected $contentEncoding = '';
    
   /**
    * @var RequestHelper_Boundaries
    */
    protected $boundaries;
    
    public function __construct(RequestHelper_Boundaries $boundaries, string $content)
    {
        $this->boundaries = $boundaries;
        $this->content = $content;
    }
    
    public function getContentLength() : int
    {
        return strlen($this->content);
    }
    
    public function setName(string $name) : RequestHelper_Boundaries_Boundary
    {
        return $this->setDispositionParam('name', $name);
    }
    
    public function setFileName(string $fileName) : RequestHelper_Boundaries_Boundary
    {
        return $this->setDispositionParam('filename', $fileName);
    }
    
    public function setContentType(string $contentType) : RequestHelper_Boundaries_Boundary
    {
        $this->contentType = $contentType;
        return $this;
    }
    
    public function setContentEncoding(string $encoding) : RequestHelper_Boundaries_Boundary
    {
        $this->contentEncoding = $encoding;
        return $this;
    }
    
    protected function setDispositionParam(string $name, string $value) : RequestHelper_Boundaries_Boundary
    {
        $this->dispositionParams[$name] = $value;
        return $this;
    }
    
    public function render()
    {
        $eol = $this->boundaries->getEOL();
        
        $lines = array();
        $lines[] = '--'.$this->boundaries->getMimeBoundary();
        $lines[] = $this->renderContentDisposition();
        
        if(!empty($this->contentType)) {
            $lines[] = $this->renderContentType();
        }
        
        $lines[] = $eol;
        $lines[] = $this->content;
        
        return implode($eol, $lines).$eol;
    }
    
    protected function renderContentDisposition() : string
    {
        $result = 'Content-Disposition: form-data';
        
        foreach($this->dispositionParams as $name => $value) 
        {
            $result .= '; '.$name.'="' . $value . '"';
        }   
        
        return $result;
    }
    
    protected function renderContentType() : string
    {
        $result = 'Content-Type: ' . $this->contentType; 
        
        if(!empty($this->contentEncoding)) 
        {
            $result .= '; charset=' . $this->contentEncoding;
        }
        
        return $result;
    }
}
