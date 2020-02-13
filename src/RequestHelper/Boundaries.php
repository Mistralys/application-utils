<?php
/**
 * File containing the {@link RequestHelper_Boundaries} class.
 * @package Application Utils
 * @subpackage RequestHelper
 * @see RequestHelper_Boundaries
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Container for the collection of boundaries that will be 
 * sent in the request.
 *
 * @package Application Utils
 * @subpackage RequestHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestHelper_Boundaries
{
    const ERROR_NO_BOUNDARIES_SPECIFIED = 44401;
    
   /**
    * @var RequestHelper
    */
    protected $helper;

   /**
    * @var RequestHelper_Boundaries_Boundary[]
    */
    protected $boundaries = array();
    
   /**
    * @var integer
    */
    protected $contentLength = 0;
    
    public function __construct(RequestHelper $helper)
    {
        $this->helper = $helper;
    }
    
   /**
    * Retrieves the string that is used to separate mime boundaries in the body.
    * 
    * @return string
    */
    public function getMimeBoundary() : string
    {
        return $this->helper->getMimeBoundary();
    }
    
   /**
    * Retrieves the end of line character(s) used in the body.
    * 
    * @return string
    */
    public function getEOL() : string
    {
        return $this->helper->getEOL();
    }
    
   /**
    * Retrieves the total content length of all boundary contents.
    * 
    * @return int
    */
    public function getContentLength() : int
    {
        return mb_strlen($this->render());
    }
    
   /**
    * Adds a file to be sent with the request.
    *
    * @param string $varName The variable name to send the file in
    * @param string $fileName The name of the file as it should be received at the destination
    * @param string $content The raw content of the file
    * @param string $contentType The content type, use the constants to specify this
    * @param string $encoding The encoding of the file, use the constants to specify this
    */
    public function addFile(string $varName, string $fileName, string $content, string $contentType = '', string $encoding = '') : RequestHelper_Boundaries
    {
        if(empty($contentType))
        {
            $contentType = FileHelper::detectMimeType($fileName);
        }
        
        if(empty($encoding))
        {
            $encoding = RequestHelper::ENCODING_UTF8;
        }
        
        $boundary = $this->createBoundary(chunk_split(base64_encode($content)))
        ->setName($varName)
        ->setFileName(basename($fileName))
        ->setContentType($contentType)
        ->setContentEncoding($encoding)
        ->setTransferEncoding(RequestHelper::TRANSFER_ENCODING_BASE64);
        
        return $this->addBoundary($boundary);
    }
    
   /**
    * Adds arbitrary content.
    *
    * @param string $varName
    * @param string $content
    * @param string $contentType
    */
    public function addContent(string $varName, string $content, string $contentType) : RequestHelper_Boundaries
    {
        $boundary = $this->createBoundary($content)
        ->setName($varName)
        ->setContentType($contentType)
        ->setContentEncoding(RequestHelper::ENCODING_UTF8);
        
        return $this->addBoundary($boundary);
    }
    
   /**
    * Adds a variable to be sent with the request. If it
    * already exists, its value is overwritten.
    *
    * @param string $name
    * @param string $value
    */
    public function addVariable(string $name, string $value) : RequestHelper_Boundaries
    {
        $boundary = $this->createBoundary($value)
        ->setName($name);
        
        return $this->addBoundary($boundary);
    }
    
    protected function addBoundary(RequestHelper_Boundaries_Boundary $boundary) : RequestHelper_Boundaries
    {
        $this->boundaries[] = $boundary;
        
        return $this;
    }
    
   /**
    * Renders the response body with all mime boundaries.
    * 
    * @return string
    */
    public function render() : string
    {
        if(empty($this->boundaries)) 
        {
            throw new RequestHelper_Exception(
                'No mime boundaries added',
                'At least one content has to be added, be it variables or files.',
                self::ERROR_NO_BOUNDARIES_SPECIFIED
            );    
        }
        
        $result = '';
        
        foreach($this->boundaries as $boundary)
        {
            $result .= $boundary->render();
        }
        
        $result .= "--" . $this->getMimeBoundary() . "--" . 
        $this->getEOL() . $this->getEOL(); // always finish with two eol's!!
        
        return $result;
    }
    
    protected function createBoundary(string $content) : RequestHelper_Boundaries_Boundary
    {
        return new RequestHelper_Boundaries_Boundary($this, $content);
    }
}
