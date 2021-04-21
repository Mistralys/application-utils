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
    const FILETYPE_HTML = 'text/html';
    const ENCODING_UTF8 = 'UTF-8';

    const TRANSFER_ENCODING_BASE64 = 'BASE64';
    const TRANSFER_ENCODING_8BIT = '8BIT';
    const TRANSFER_ENCODING_BINARY = 'BINARY';
    
    const ERROR_REQUEST_FAILED = 17902;
    const ERROR_CURL_INIT_FAILED = 17903;
    const ERROR_CANNOT_OPEN_LOGFILE = 17904;

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
    protected $destination;

   /**
    * @var array<string,string>
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
    * Timeout duration, in seconds.
    * @var integer
    */
    protected $timeout = 30;
    
   /**
    * @var string
    */
    protected $logfile = '';

   /**
    * @var resource|NULL
    */
    protected $logfilePointer = null;
    
   /**
    * Creates a new request helper to send POST data to the specified destination URL.
    * @param string $destinationURL
    */
    public function __construct(string $destinationURL)
    {
        $this->destination = $destinationURL;
        $this->mimeBoundary = str_repeat('-', 20).md5('request-helper-boundary');
        $this->boundaries = new RequestHelper_Boundaries($this);
    }
    
    public function getMimeBoundary() : string
    {
        return $this->mimeBoundary;
    }
    
    public function getMimeBody() : string
    {
        return $this->boundaries->render();
    }
    
    public function getEOL() : string
    {
        return $this->eol;
    }
    
   /**
    * Sets the timeout for the request, in seconds. If the request
    * takes longer, it will be cancelled and an exception triggered.
    * 
    * @param int $seconds
    * @return RequestHelper
    */
    public function setTimeout(int $seconds) : RequestHelper
    {
        $this->timeout = $seconds;
        
        return $this;
    }
    
    public function getTimeout() : int
    {
        return $this->timeout;
    }
    
   /**
    * Enables verbose logging of the CURL request, which
    * is then redirected to the target file.
    * 
    * @param string $targetFile
    * @return RequestHelper
    */
    public function enableLogging(string $targetFile) : RequestHelper
    {
        $this->logfile = $targetFile;
        
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
    public function addFile(string $varName, string $fileName, string $content, string $contentType = '', string $encoding = '') : RequestHelper
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
        $info = parseURL($this->destination);
        
        $ch = $this->createCURL($info);

        $output = curl_exec($ch);

        if(isset($this->logfilePointer))
        {
            fclose($this->logfilePointer);
        }
        
        $info = curl_getinfo($ch);
        
        $this->response = new RequestHelper_Response($this, $info);
        
        // CURL will complain about an empty response when the 
        // server sends a 100-continue code. That should not be
        // regarded as an error.
        if($output === false && $this->response->getCode() !== 100)
        {
            $curlCode = curl_errno($ch);
            
            $this->response->setError(
                $curlCode,
                curl_error($ch).' | Explanation: '.curl_strerror($curlCode)
            );
        }
        else
        {
            $this->response->setBody((string)$output);
        }
        
        curl_close($ch);
        
        return $this->response->getResponseBody();
    }
    
   /**
    * Retrieves the request's body content. This is an alias
    * for {@see RequestHelper::getMimeBody()}.
    * 
    * @return string
    * @see RequestHelper::getMimeBody()
    */
    public function getBody() : string
    {
        return $this->getMimeBody();
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
        
        if(!is_resource($ch))
        {
            throw new RequestHelper_Exception(
                'Could not initialize a new cURL instance.',
                'Calling curl_init returned false. Additional information is not available.',
                self::ERROR_CURL_INIT_FAILED
            );
        }

        $this->setHeader('Content-Length', (string)$this->boundaries->getContentLength());
        $this->setHeader('Content-Type', 'multipart/form-data; boundary=' . $this->mimeBoundary);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url->getNormalizedWithoutAuth());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->renderHeaders());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->boundaries->render());
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $loggingEnabled = $this->configureLogging($ch);
        
        if(!$loggingEnabled) 
        {
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        }
        
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
    
   /**
    * @param resource $ch
    * @return bool Whether logging is enabled.
    */
    protected function configureLogging($ch) : bool
    {
        if(empty($this->logfile))
        {
            return false;
        }
        
        $res = fopen($this->logfile, 'w+');
        
        if($res === false)
        {
            throw new RequestHelper_Exception(
                'Cannot open logfile for writing.',
                sprintf('Tried accessing the file at [%s].', $this->logfile),
                self::ERROR_CANNOT_OPEN_LOGFILE
            );
        }
        
        $this->logfilePointer = $res;
        
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $res);
        
        return true;
    }

   /**
    * Compiles the associative headers array into
    * the format understood by CURL, namely an indexed
    * array with one header string per entry.
    * 
    * @return array
    */
    protected function renderHeaders() : array
    {
        $result = array();
        
        $this->setHeader('Expect', '');
        
        foreach($this->headers as $name => $value) {
            $result[] = $name.': '.$value;
        }
        
        return $result;
    }
    
   /**
    * Retrieves the raw response header, in the form of an indexed
    * array containing all response header lines.
    * 
    * @return array
    */
    public function getResponseHeader() : array
    {
        $response = $this->getResponse();
        
        if($response !== null) {
            return $response->getHeaders();
        }

        return array();
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
    
   /**
    * Retrieves all headers set until now.
    * 
    * @return array<string,string>
    */
    public function getHeaders() : array
    {
        return $this->headers;
    }
    
   /**
    * Retrieves the value of a header by its name.
    * 
    * @param string $name
    * @return string The header value, or an empty string if not set.
    */
    public function getHeader(string $name) : string
    {
        if(isset($this->headers[$name]))
        {
            return $this->headers[$name];
        }
        
        return '';
    }
}
