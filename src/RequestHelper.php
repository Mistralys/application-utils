<?php
/**
 * File containing the {@link RequestHelper} class.
 * @package Application Utils
 * @subpackage RequestHelper
 * @see RequestHelper
 */

namespace AppUtils;

/**
 * Handles sending POST requests with file attachments and regular variables.
 * Creates the raw request headers required for the request and sends them
 * using file_get_contents with the according context parameters.
 *
 * @package Application Utils
 * @subpackage RequestHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestHelper
{
    const FILETYPE_TEXT = 'text/plain';

    const FILETYPE_XML = 'text/xml';

    const ENCODING_UTF8 = 'UTF-8';

    const ERROR_REQUEST_FAILED = 17902;
    
    const ERROR_CURL_INIT_FAILED = 17903;

   /**
    * @var string
    */
    protected $eol = "\r\n";

   /**
    * @var string
    */
    protected $mimeBoundary;

   /**
    * @var string
    */
    protected $data = '';

   /**
    * @var string
    */
    protected $destination;

   /**
    * @var array
    */
    protected $headers = array();
    
   /**
    * Whether to verify SSL certificates.
    * @var bool
    */
    protected $verifySSL = true;
    
   /**
    * @var RequestHelper_Boundaries
    */
    protected $boundaries;
    
   /**
    * @var RequestHelper_Response|NULL
    */
    protected $response;

   /**
    * @var integer
    */
    protected $timeout = 30;
    
   /**
    * Creates a new request helper to send POST data to the specified destination URL.
    * @param string $destinationURL
    */
    public function __construct(string $destinationURL)
    {
        $this->destination = $destinationURL;
        $this->mimeBoundary = md5('request-helper-boundary');
        $this->boundaries = new RequestHelper_Boundaries($this);
        
        requireCURL();
    }
    
    public function getMimeBoundary() : string
    {
        return $this->mimeBoundary;
    }
    
    public function getEOL() : string
    {
        return $this->eol;
    }
    
    public function setTimeout(int $seconds) : RequestHelper
    {
        $this->timeout = $seconds;
        
        return $this;
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
    public function addFile(string $varName, string $fileName, string $content, string $contentType = self::FILETYPE_TEXT, string $encoding = self::ENCODING_UTF8) : RequestHelper
    {
        $this->boundaries->addFile($varName, $fileName, $content, $contentType, $encoding);
        
        return $this;
    }
    
   /**
    * Adds arbitrary content.
    * 
    * @param string $varName The variable name to send the content in.
    * @param string $content
    * @param string $contentType
    */
    public function addContent(string $varName, string $content, string $contentType) : RequestHelper
    {
        $this->boundaries->addContent($varName, $content, $contentType);
        
        return $this;
    }

    /**
     * Adds a variable to be sent with the request. If it
     * already exists, its value is overwritten.
     *
     * @param string $name
     * @param string $value
     */
    public function addVariable(string $name, string $value) : RequestHelper
    {
        $this->boundaries->addVariable($name, $value);
        
        return $this;
    }
    
   /**
    * Sets an HTTP header to include in the request.
    * 
    * @param string $name
    * @param string $value
    * @return RequestHelper
    */
    public function setHeader(string $name, string $value) : RequestHelper
    {
        $this->headers[$name] = $value;
        
        return $this;
    }
    
   /**
    * Disables SSL certificate checking.
    * 
    * @return RequestHelper
    */
    public function disableSSLChecks() : RequestHelper
    {
        $this->verifySSL = false;
        return $this;
    }
   
   /**
    * @var integer
    */
    protected $contentLength = 0;

   /**
    * Sends the POST request to the destination, and returns
    * the response text.
    *
    * The response object is stored internally, so after calling
    * this method it may be retrieved at any moment using the
    * {@link getResponse()} method.
    *
    * @return string
    * @see RequestHelper::getResponse()
    * @throws RequestHelper_Exception
    * 
    * @see RequestHelper::ERROR_REQUEST_FAILED
    */
    public function send() : string
    {
        $this->data = $this->boundaries->render();

        $info = parseURL($this->destination);
        
        $ch = $this->createCURL($info);
        
        $output = curl_exec($ch);

        $info = curl_getinfo($ch);

        $this->response = new RequestHelper_Response($this, $info);
        
        // CURL will complain about an empty response when the 
        // server sends a 100-continue code. That should not be
        // regarded as an error.
        if($output === false && $this->response->getCode() !== 100)
        {
            $this->response->setError(
                curl_errno($ch),
                curl_error($ch)
            );
        }
        else
        {
            $this->response->setBody($output);
        }
        
        curl_close($ch);
        
        return $this->response->getResponseBody();
    }
    
    public function getBody() : string
    {
        return $this->data;
    }
    
   /**
    * Creates a new CURL resource configured according to the
    * request's settings.
    * 
    * @param URLInfo $url
    * @throws RequestHelper_Exception
    * @return resource
    */
    protected function createCURL(URLInfo $url)
    {
        $ch = curl_init();
        if($ch === false)
        {
            throw new RequestHelper_Exception(
                'Could not initialize a new cURL instance.',
                'Calling curl_init returned false. Additional information is not available.',
                self::ERROR_CURL_INIT_FAILED
            );
        }

        $this->setHeader('Content-Length', $this->boundaries->getContentLength());
        $this->setHeader('Content-Type', 'multipart/form-data; charset=UTF-8; boundary=' . $this->mimeBoundary);
        
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url->getNormalizedWithoutAuth());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->renderHeaders());
        
        if($this->verifySSL)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        
        if($url->hasUsername())
        {
            curl_setopt($ch, CURLOPT_USERNAME, $url->getUsername());
            curl_setopt($ch, CURLOPT_PASSWORD, $url->getPassword());
        }
        
        return $ch;
    }

    protected function renderHeaders() : array
    {
        $result = array();
        
        foreach($this->headers as $name => $value) {
            $result[] = $name.': '.$value;
        }
        
        return $result;
    }
    
   /**
    * Retrieves the raw response header, in the form of an indexed
    * array containing all response header lines, for example:
    */
    public function getResponseHeader()
    {
        return $this->response->getInfo();
    }

    /**
     * After calling the {@link send()} method, this may be used to
     * retrieve the response text from the POST request.
     *
     * @return RequestHelper_Response|NULL
     */
    public function getResponse() : ?RequestHelper_Response
    {
        return $this->response;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
}
