<?php
/**
 * File containing the {@link RequestHelper} class.
 * @package Helpers
 * @subpackage RequestHelper
 * @see RequestHelper
 */

/**
 * Handles sending POST requests with file attachments and regular variables.
 * Creates the raw request headers required for the request and sends them
 * using file_get_contents with the according context parameters.
 *
 * @package Helpers
 * @subpackage RequestHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestHelper
{
    const FILETYPE_TEXT = 'text/plain';

    const FILETYPE_XML = 'text/xml';

    const ENCODING_UTF8 = 'UTF-8';

    const ERROR_REQUEST_FAILED = 17902;
    
    protected $eol = "\r\n";

    protected $mimeBoundary;

    protected $data = '';

    protected $destination;

    /**
     * Creates a new request helper to send POST data to the specified destination URL.
     * @param string $destination
     */
    public function __construct($destination)
    {
        $this->destination = $destination;
        $this->mimeBoundary = '----------------super_boundary';
    }

    protected $files = array();

    /**
     * Adds a file to be sent with the request.
     *
     * @param string $varName The variable name to send the file in
     * @param string $fileName The name of the file as it should be received at the destination
     * @param string $content The raw content of the file
     * @param string $contentType The content type, use the constants to specify this
     * @param string $encoding The encoding of the file, use the constants to specify this
     */
    public function addFile($varName, $fileName, $content, $contentType = self::FILETYPE_TEXT, $encoding = self::ENCODING_UTF8)
    {
        $this->files[$varName] = array(
            'fileName' => basename($fileName),
            'content' => chunk_split(base64_encode($content)),
            'contentType' => $contentType,
            'encoding' => $encoding
        );
    }

    protected $variables = array();

    /**
     * Adds a variable to be sent with the request.
     *
     * @param string $name
     * @param string $value
     */
    public function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Sends the POST request to the destination, and returns
     * the response text. Use the {@link getResponseHeader()}
     * method to retrieve the response header if need be.
     *
     * The response text is stored internally, so after calling
     * this method it may be retrieved at any moment using the
     * {@link getResponse()} method.
     *
     * @return string
     * @see getResponse()
     * @see getResponseHeader()
     */
    public function send()
    {
        $contentLength = 0;
        if (!empty($this->variables)) {
            foreach ($this->variables as $name => $value) {
                $this->data .= '--' . $this->mimeBoundary . $this->eol;
                $this->data .= 'Content-Disposition: form-data; name="' . $name . '"' . $this->eol;
                $this->data .= $this->eol;
                $this->data .= $value . $this->eol;

                $contentLength += strlen($value);
            }
        }

        if (!empty($this->files)) {
            foreach ($this->files as $varName => $def) {
                $this->data .= '--' . $this->mimeBoundary . $this->eol;
                $this->data .= 'Content-Disposition: form-data; name="' . $varName . '"; filename="' . $def['fileName'] . '"' . $this->eol;
                $this->data .= 'Content-Type: ' . $def['contentType'] . '; charset=' . $def['encoding'];
                $this->data .= $this->eol . $this->eol;
                $this->data .= $def['content'] . $this->eol;

                $contentLength += strlen($def['content']);
            }
        }

        $this->data .= "--" . $this->mimeBoundary . "--" . $this->eol . $this->eol; // finish with two eol's!!

        //echo '<pre>'.print_r($this->data,true).'</pre>';

        $params = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                    'Content-Length: ' . $contentLength . $this->eol .
                    'Content-Type: multipart/form-data; charset=UTF-8; boundary=' . $this->mimeBoundary . $this->eol
            ,
                'content' => $this->data
            )
        );

        global $http_response_header;
        $ctx = stream_context_create($params);

        $this->response = @file_get_contents($this->destination, null, $ctx);
        if ($this->response === false) {
            $errorMessage = error_get_last();
            throw new RequestHelper_Exception(
                'Request failed.',
                'Request error message: ' . $errorMessage['message'] . ' in ' . $errorMessage['file'] . ':' . $errorMessage['line'],
                self::ERROR_REQUEST_FAILED
            );
        }

        $this->responseHeader = $http_response_header;

        //echo '<pre>'.print_r($this->responseHeader,true).'</pre>';
        //exit;

        return $this->response;
    }

    protected $response;

    protected $responseHeader;

    /**
     * Retrieves the raw response header, in the form of an indexed
     * array containing all response header lines, for example:
     *
     * Array
     * (
     *     [0] => HTTP/1.1 200 OK
     *     [1] => Date: Thu, 31 Jan 2013 10:44:21 GMT
     *     [2] => Server: Apache/2.2.22 (Win32) mod_ssl/2.2.22 OpenSSL/0.9.8o
     *     [3] => X-Powered-By: PHP/5.3.14 ZendServer
     *     [4] => Set-Cookie: ZDEDebuggerPresent=php,phtml,php3; path=/
     *     [5] => Content-Disposition: attachment; filename=converted.csv
     *     [6] => Pragma: no-cache
     *     [7] => Connection: close
     *     [8] => Content-Type: text/csv; charset=utf-8
     * )
     */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * After calling the {@link send()} method, this may be used to
     * retrieve the response text from the POST request.
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}

class RequestHelper_Exception extends Exception
{
    protected $details;
    
    public function __construct($message, $details=null, $code=null, $previous=null)
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }
    
    public function getDetails()
    {
        return $this->details;
    }
}