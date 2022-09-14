<?php
/**
 * File containing the {@link Request} class.
 * @package Application Utils
 * @subpackage Request
 * @see Request
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\Request\RequestParam;
use JsonException;
use stdClass;

/**
 * Request management: wrapper around request variables with validation
 * capabilities and overall easier and more robust request variable handling.
 *
 * Usage:
 *
 * // get a parameter. If it does not exist, returns null.
 * $request->getParam('name');
 *
 * // get a parameter and specify the default value to return if it does not exist.
 * $request->getParam('name', 'Default value');
 *
 * // register a parameter to specify its validation: if the existing
 * // value does not match the type, it will be considered inexistent.
 * $request->registerParam('name')->setInteger();
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request
{
    public const ERROR_MISSING_OR_INVALID_PARAMETER = 97001;
    public const ERROR_PARAM_NOT_REGISTERED = 97002;
    
    protected static ?Request $instance = null;
    protected string $baseURL = '';

    /**
     * Stores registered parameter objects.
     * @see registerParam()
     *@var RequestParam[]
     */
    protected array $knownParams = array();

    public function __construct()
    {
        self::$instance = $this;
        
        $this->init();
    }
    
   /**
    * Can be extended in a subclass, to avoid
    * redefining the constructor.
    *
    * @return void
    */
    protected function init() : void
    {
        
    }
    
    /**
     * @return Request
     */
    public static function getInstance() : self
    {
        return self::$instance ?? new Request();
    }
    
    /**
     * Retrieves the value of a request parameter. Note that these values
     * are NOT validated unless they have been specifically registered
     * using the {@link registerParam()} method.
     *
     * If the request parameter has not been set or its value is empty,
     * the specified default value is returned.
     *
     * @param string $name
     * @param mixed|NULL $default
     * @return mixed|NULL
     */
    public function getParam(string $name, $default = null)
    {
        $value = $_REQUEST[$name] ?? $default;

        if(isset($this->knownParams[$name])) {
            $value = $this->knownParams[$name]->validate($value);
        }
        
        return $value;
    }

    /**
     * @return array<mixed>
     */
    public function getParams() : array
    {
        return $_REQUEST;
    }
    
    /**
     * Builds a URL to refresh the current page: includes all currently
     * specified request variables, with the option to overwrite/specify
     * new ones via the params parameter.
     *
     * @param array<string,string|number> $params
     * @param string[] $exclude Names of parameters to exclude from the refresh URL.
     * @return string
     * 
     * @see Request::getRefreshParams()
     */
    public function buildRefreshURL(array $params = array(), array $exclude = array()) : string
    {
        $params = $this->getRefreshParams($params, $exclude);
        
        $dispatcher = $this->getDispatcher();
        
        return $this->buildURL($params, $dispatcher);
    }
    
   /**
    * Retrieves the name of the current dispatcher script / page.
    * This is made to be extended and implemented in a subclass.
    * 
    * @return string
    */
    public function getDispatcher() : string
    {
        return '';
    }
    
   /**
    * Filters and retrieves the current request variables 
    * to be used to build a URL to refresh the current page.
    * 
    * For further customization options, use the 
    * {@see Request::createRefreshParams()} method.
    * 
    * @param array<string,mixed> $params Key => value pairs of parameters to always include in the result.
    * @param string[] $exclude Names of parameters to exclude from the result.
    * @return array<string,mixed>
    * 
    * @see Request::createRefreshParams()
    */
    public function getRefreshParams(array $params = array(), array $exclude = array()) : array
    {
        return $this->createRefreshParams()
            ->overrideParams($params)
            ->excludeParamsByName($exclude)
            ->getParams();
    }
    
   /**
    * Creates an instance of the helper that can be used to
    * retrieve the request's parameters collection, with the
    * possibility to exclude and override some by rules.
    * 
    * @return Request_RefreshParams
    */
    public function createRefreshParams() : Request_RefreshParams
    {
        return new Request_RefreshParams();
    }

    /**
     * @return string[]
     */
    public function getExcludeParams() : array
    {
        return array();
    }
    
    /**
     * Builds an application URL using the specified parameters: returns
     * an absolute URL to the main dispatcher with the specified parameters.
     * Not specifying any parameters returns the absolute URL to the
     * application, without ending slash.
     *
     * @param array<string,mixed> $params
     * @param string $dispatcher Relative path to script to use for the URL. Append trailing slash if needed.
     * @return string
     */
    public function buildURL(array $params = array(), string $dispatcher='') : string
    {
        $url = rtrim($this->getBaseURL(), '/') . '/' . $dispatcher;
        
        // append any leftover parameters to the end of the URL
        if (!empty($params)) {
            $url .= '?' . http_build_query($params, '', '&amp;');
        }
        
        return $url;
    }
    
   /**
    * Retrieves the base URL of the application.
    * @return string
    */
    public function getBaseURL() : string
    {
        return $this->baseURL;
    }
    
    public function setBaseURL(string $url) : Request
    {
        $this->baseURL = $url;
        return $this;
    }
    
    /**
     * Registers a known parameter by name, allowing you to set validation
     * rules for the parameter. Returns the parameter object, so you can
     * configure it directly by chaining.
     *
     * @param string $name
     * @return RequestParam
     */
    public function registerParam(string $name) : RequestParam
    {
        if(!isset($this->knownParams[$name])) {
            $param = new RequestParam($this, $name);
            $this->knownParams[$name] = $param;
        }
        
        return $this->knownParams[$name];
    }
    
   /**
    * Retrieves a previously registered parameter instance.
    * 
    * @param string $name
    * @return RequestParam
    *@throws Request_Exception
    */
    public function getRegisteredParam(string $name) : RequestParam
    {
        if(isset($this->knownParams[$name])) {
            return $this->knownParams[$name];
        }
        
        throw new Request_Exception(
            'Unknown registered request parameter.',
            sprintf(
                'The request parameter [%s] has not been registered.',
                $name
            ),
            self::ERROR_PARAM_NOT_REGISTERED
        );
    }
    
   /**
    * Checks whether a parameter with the specified name 
    * has been registered.
    * 
    * @param string $name
    * @return bool
    */
    public function hasRegisteredParam(string $name) : bool
    {
        return isset($this->knownParams[$name]);
    }
    
   /**
    * Retrieves an indexed array with accept mime types
    * that the client sent, in the order of preference
    * the client specified.
    *
    * Example:
    *
    * array(
    *     'text/html',
    *     'application/xhtml+xml',
    *     'image/webp'
    *     ...
    * )
    * 
    * @return string[]
    * @see Request::parseAcceptHeaders()
    */
    public static function getAcceptHeaders() : array
    {
        return self::parseAcceptHeaders()->getMimeStrings();
    }
    
   /**
    * Returns an instance of the "accept" headers parser,
    * to access information on the browser's accepted
    * mime types.
    *  
    * @return Request_AcceptHeaders
    * @see Request::getAcceptHeaders()
    */
    public static function parseAcceptHeaders() : Request_AcceptHeaders
    {
        static $accept;
        
        if(!isset($accept)) {
            $accept = new Request_AcceptHeaders();
        }
        
        return $accept;
    }
    
    /**
     * Sets a request parameter. Does nothing more than setting/overwriting
     * a parameter value within the same request.
     *
     * @param string $name
     * @param mixed $value
     * @return Request
     */
    public function setParam(string $name, $value) : Request
    {
        $_REQUEST[$name] = $value;
        
        if(isset($this->knownParams[$name])) {
            unset($this->knownParams[$name]);
        }
        
        return $this;
    }

    /**
     * Checks whether the specified param exists in the current request.
     * Note: if the parameter exists, but is not valid according to the
     * parameter definition, it is assumed it does not exist.
     *
     * @param string $name
     * @return boolean
     */
    public function hasParam(string $name) : bool
    {
        return $this->getParam($name) !== null;
    }
    
   /**
    * Removes a single parameter from the request.
    * If the parameter has been registered, also
    * removes the registration info.
    * 
    * @param string $name
    * @return Request
    */
    public function removeParam(string $name) : Request
    {
        if(isset($_REQUEST[$name])) {
            unset($_REQUEST[$name]);
        }
        
        if(isset($this->knownParams[$name])) {
            unset($this->knownParams[$name]);
        }
        
        return $this;
    }
    
   /**
    * Removes several parameters from the request.
    * 
    * @param string[] $names
    * @return Request
    */
    public function removeParams(array $names) : Request
    {
        foreach($names as $name) {
            $this->removeParam($name);
        }
        
        return $this;
    }

    /**
     * Treats the request parameter as a boolean parameter
     * and returns its value as a boolean. If it does not exist
     * or does not have a value convertable to a boolean,
     * returns false.
     *
     * @param string $name
     * @param bool $default
     * @return bool
     * @throws ConvertHelper_Exception
     */
    public function getBool(string $name, bool $default=false) : bool
    {
        $value = $this->getParam($name, $default);

        if(ConvertHelper::isBoolean($value)) {
            return ConvertHelper::string2bool($value);
        }
        
        return false;
    }
    
    public function validate() : void
    {
        foreach($this->knownParams as $param) 
        {
            $name = $param->getName();
            
            if($param->isRequired() && !$this->hasParam($name)) 
            {
                throw new Request_Exception(
                    'Missing request parameter '.$name,
                    sprintf(
                        'The request parameter [%s] is required, and is either empty or invalid.',
                        $name
                    ),
                    self::ERROR_MISSING_OR_INVALID_PARAMETER
                );
            }
        }
    }
    
    /**
     * Retrieves a param, filtered to remove HTML tags and with
     * html special characters encoded to avoid XSS injections.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getFilteredParam(string $name, $default=null)
    {
        $val = $this->getParam($name, $default);

        if(is_string($val))
        {
            return htmlspecialchars(trim(strip_tags($val)), ENT_QUOTES, 'UTF-8');
        }

        if(is_bool($val))
        {
            return ConvertHelper::boolStrict2string($val);
        }

        if(is_numeric($val))
        {
            return (string)$val;
        }

        if(is_null($val))
        {
            return '';
        }

        return $val;
    }

    /**
     * Treats the request parameter as a JSON string, and
     * if it exists and contains valid JSON, returns the
     * decoded JSON value as an array (default).
     *
     * @param string $name
     * @param bool $assoc
     * @return array<mixed>|object
     *
     * @see Request::getJSONObject()
     * @see Request::getJSONAssoc()
     */
    public function getJSON(string $name, bool $assoc=true)
    {
        $value = $this->getParam($name);
        
        if(!empty($value) && is_string($value)) 
        {
            $value = JSONConverter::json2varSilent($value, $assoc);

            if($assoc && is_array($value)) {
                return $value;
            }
            
            if(is_object($value)) {
                return $value;
            }
        }
        
        if($assoc) {
            return array();
        }
        
        return new stdClass();
    }

    /**
     * Like {@link Request::getJSON()}, but omitting the second
     * parameter. Use this for more readable code.
     *
     * @param string $name
     * @return array<mixed>
     */
    public function getJSONAssoc(string $name) : array
    {
        $result = $this->getJSON($name);
        if(is_array($result)) {
            return $result;
        }
        
        return array();
    }

    /**
     * Like {@link Request::getJSON()}, but omitting the second
     * parameter. Use this for more readable code.
     *
     * @param string $name
     * @return object
     */
    public function getJSONObject(string $name) : object
    {
        $result = $this->getJSON($name, false);
        if(is_object($result)) {
            return $result;
        }
        
        return new stdClass();
    }

    /**
     * Sends a JSON response with the correct headers.
     *
     * @param array<mixed>|string $data
     * @throws JSONConverterException
     */
    public static function sendJSON($data) : void
    {
        $payload = $data;

        if(!is_string($payload)) {
            $payload = JSONConverter::var2json($payload);
        }
        
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        
        echo $payload;
    }

    /**
     * @param array<mixed>|string $data
     * @return never
     * @throws JSONConverterException
     */
    public static function sendJSONAndExit($data) : void
    {
        self::sendJSON($data);
        exit;
    }
    
   /**
    * Sends HTML to the browser with the correct headers.
    * 
    * @param string $html
    */
    public static function sendHTML(string $html) : void
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: text/html; charset=utf-8');
        
        echo $html;
    }

    /**
     * @param string $html
     * @return never
     */
    public static function sendHTMLAndExit(string $html) : void
    {
        self::sendHTML($html);

        exit;
    }
    
   /**
    * Creates a new instance of the URL comparer, which can check 
    * whether the specified URLs match, regardless of the order in 
    * which the query parameters are, if any.
    * 
    * @param string $sourceURL
    * @param string $targetURL
    * @param string[] $limitParams Whether to limit the comparison to these specific parameter names (if present)
    * @return Request_URLComparer
    */
    public function createURLComparer(string $sourceURL, string $targetURL, array $limitParams=array()) : Request_URLComparer
    {
        $comparer = new Request_URLComparer($this, $sourceURL, $targetURL);
        $comparer->addLimitParams($limitParams);
        
        return $comparer;
    }
    
   /**
    * Retrieves the full URL that was used to access the current page.
    * @return string
    */
    public function getCurrentURL() : string
    {
        return $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }
}