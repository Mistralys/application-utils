<?php
/**
 * File containing the {@link RequestHelper_Response} class.
 * @package Application Utils
 * @subpackage RequestHelper
 * @see RequestHelper_Response
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Stores information on the response to a request that was sent.
 *
 * @package Application Utils
 * @subpackage RequestHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestHelper_Response
{
   /**
    * @var RequestHelper
    */
    protected $request;
    
   /**
    * @var string
    */
    protected $body = '';
    
   /**
    * @var array
    */
    protected $info;
    
   /**
    * @var bool
    */
    protected $isError = false;
    
   /**
    * @var string
    */
    protected $errorMessage = '';
    
   /**
    * @var integer
    */
    protected $errorCode = 0;
    
   /**
    * @param RequestHelper $helper
    * @param array $info The CURL info array from curl_getinfo().
    */
    public function __construct(RequestHelper $helper, array $info)
    {
        $this->request = $helper;
        $this->info = $info;
    }
    
   /**
    * Retrieves the request instance that initiated the request.
    *  
    * @return RequestHelper
    */
    public function getRequest() : RequestHelper
    {
        return $this->request;
    }
    
    public function setError(int $code, string $message) : RequestHelper_Response
    {
        $this->errorMessage = $message;
        $this->errorCode = $code;
        $this->isError = true;
        
        return $this;
    }
    
    public function setBody(string $body) : RequestHelper_Response
    {
        $this->body = $body;
        return $this;
    }
    
   /**
    * Whether an error occurred in the request.
    * @return bool
    */
    public function isError() : bool
    {
        return $this->isError;
    }
    
   /**
    * Whether the request timed out.
    * @return bool
    */
    public function isTimeout() : bool
    {
        return $this->errorCode === RequestHelper_CURL::OPERATION_TIMEDOUT;
    }
    
   /**
    * Retrieves the native error message, if an error occurred.
    * @return string
    */
    public function getErrorMessage() : string
    {
        return $this->errorMessage;
    }
    
   /**
    * Retrieves the native CURL error code, if an error occurred.
    * @return int
    * @see RequestHelper_CURL For a list of error codes.
    */
    public function getErrorCode() : int
    {
        return $this->errorCode;
    }
    
   /**
    * Retrieves the full body of the request.
    * 
    * @return string
    */
    public function getRequestBody() : string
    {
        return $this->request->getBody();
    }
    
   /**
    * Retrieves the body of the response, if any.
    * 
    * @return string
    */
    public function getResponseBody() : string
    {
        return $this->body;
    }
    
   /**
    * The response HTTP code.
    * 
    * @return int The code, or 0 if none was sent (on error).
    */
    public function getCode() : int
    {
        return intval($this->getInfoKey('http_code'));
    }
    
   /**
    * Retrieves the actual headers that were sent in the request:
    * one header by entry in the indexed array.
    * 
    * @return array
    */
    public function getHeaders() : array
    {
        return ConvertHelper::explodeTrim("\n", $this->getInfoKey('request_header'));
    }
    
    protected function getInfoKey(string $name) : string
    {
        if(isset($this->info[$name])) {
            return (string)$this->info[$name];
        }
        
        return '';
    }
}
