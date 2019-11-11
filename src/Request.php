<?php
/**
 * File containing the {@link Request} class.
 * @package Application Utils
 * @subpackage Request
 * @see Request
 */

namespace AppUtils;

/**
 * Request management: wrapper around request variables with validation
 * capabilities and overall easier and more robust request variable handling.
 *
 * Usage:
 *
 * // get a parameter. If it does not exist, returns null.
 * $request->getParam('name');
 *
 * // get a parameter and specifiy the default value to return if it does not exist.
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
    const ERROR_MISSING_OR_INVALID_PARAMETER = 97001;
    
    const ERROR_PARAM_NOT_REGISTERED = 97002;
    
    /**
     * @var Request
     */
    protected static $instance;
    
    public function __construct()
    {
        self::$instance = $this;
    }
    
    /**
     * @return Request
     */
    public static function getInstance()
    {
        return self::$instance;
    }
    
    /**
     * Stores registered parameter objects.
     * @var Request_Param[]
     * @see registerParam()
     */
    protected $knownParams = array();
    
    /**
     * Retrieves the value of a request parameter. Note that these values
     * are NOT validated unless they have been specifically registered
     * using the {@link registerParam()} method.
     *
     * If the request parameter has not been set or its value is empty,
     * the specified default value is returned.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (isset($_REQUEST[$name])) {
            $value = $_REQUEST[$name];
            if (isset($this->knownParams[$name])) {
                $value = $this->knownParams[$name]->validate($value);
            }
            
            return $value;
        }
        
        return $default;
    }
    
    public function getParams()
    {
        return $_REQUEST;
    }
    
    /**
     * Builds an URL to refresh the current page: includes all currently
     * specified request variables, with the option to overwrite/specify
     * new ones via the params parameter.
     *
     * @param array $params
     * @param string[] $exclude Names of parameters to exclude from the refresh URL.
     * @return string
     */
    public function buildRefreshURL($params = array(), $exclude = array())
    {
        $params = $this->getRefreshParams($params, $exclude);
        
        $dispatcher = $this->getDispatcher();
        
        return $this->buildURL($params, $dispatcher);
    }
    
    public function getDispatcher()
    {
        return null;
    }
    
    public function getRefreshParams($params = array(), $exclude = array())
    {
        if(empty($params)) { $params = array(); }
        if(empty($exclude)) { $exclude = array(); }
        
        $vars = $_REQUEST;

        $exclude[] = session_name();
        $exclude[] = 'ZDEDebuggerPresent';
        
        $exclude = array_merge($exclude, $this->getExcludeParams());
        
        foreach ($exclude as $name) {
            if (isset($vars[$name])) {
                unset($vars[$name]);
            }
        }
        
        $names = array_keys($vars);
        
        // remove the quickform form variable if present, to 
        // avoid redirect loops when using the refresh URL in
        // a page in which a form has been submitted.
        foreach($names as $name) {
            if(strstr($name, '_qf__')) {
                unset($vars[$name]);
                break;
            }
        }
        
        // to allow specifiying even exluded parameters
        $params = array_merge($vars, $params);
        
        return $params;
    }
    
    public function getExcludeParams()
    {
        return array();
    }
    
    /**
     * Builds an application URL using the specified parameters: returns
     * an absolute URL to the main dispatcher with the specified parameters.
     * Not specifiying any parameters returns the absolute URL to the
     * application, without ending slash.
     *
     * @param array $params
     * @param string $dispatcher Relative path to script to use for the URL. Append trailing slash if needed.
     * @return string
     */
    public function buildURL($params = array(), $dispatcher=null)
    {
        $url = rtrim(APP_URL, '/') . '/' . $dispatcher;
        
        // append any leftover parameters to the end of the URL
        if (!empty($params)) {
            $url .= '?' . http_build_query($params, null, '&amp;');
        }
        
        return $url;
    }
    
    /**
     * Registers a known parameter by name, allowing you to set validation
     * rules for the parameter. Returns the parameter object, so you can
     * configure it directly by chaining.
     *
     * @param string $name
     * @return Request_Param
     */
    public function registerParam($name)
    {
        if(!isset($this->knownParams[$name])) {
            $param = new Request_Param($this, $name);
            $this->knownParams[$name] = $param;
        }
        
        return $this->knownParams[$name];
    }
    
   /**
    * Retrieves a previously registered parameter instance.
    * 
    * @param string $name
    * @throws Request_Exception
    * @return Request_Param
    */
    public function getRegisteredParam(string $name) : Request_Param
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
    
    protected static $acceptHeaders;
    
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
     */
    public static function getAcceptHeaders()
    {
        if (isset(self::$acceptHeaders)) {
            return self::$acceptHeaders;
        }
        
        self::$acceptHeaders = array();
        
        $acceptHeader = $_SERVER['HTTP_ACCEPT'];
        
        $accept = array();
        foreach (preg_split('/\s*,\s*/', $acceptHeader) as $i => $term) 
        {
            $entry = array(
                'pos' => $i,
                'params' => array(),
                'quality' => 0,
                'type' => null
            );
            
            $matches = null;
            if (preg_match('/^(\S+)\s*;(.*)/six', $term, $matches)) 
            {
                $entry['type'] = $matches[1];
                
                if(isset($matches[2]) && !empty($matches[2])) 
                {
                    $params = ConvertHelper::parseQueryString($matches[2]);
                    $entry['params'] = $params;
                     
                    if(isset($params['q'])) {
                        $entry['quality'] = (double)$params['q'];
                    }
                }
            }
            else
            {
                $entry['type'] = $term;
            }
            
            $accept[] = $entry;
        }
        
        usort($accept, array(Request::class, 'sortAcceptHeaders'));
        
        foreach ($accept as $a) {
            self::$acceptHeaders[] = $a['type'];
        }
        
        return self::$acceptHeaders;
    }
    
    public static function sortAcceptHeaders($a, $b)
    {
        /* first tier: highest q factor wins */
        $diff = $b['quality'] - $a['quality'];
        if ($diff > 0) {
            $diff = 1;
        } else {
            if ($diff < 0) {
                $diff = -1;
            } else {
                /* tie-breaker: first listed item wins */
                $diff = $a['pos'] - $b['pos'];
            }
        }
        
        return $diff;
    }
    
    /**
     * Sets a request parameter. Does nothing more than setting/overwriting
     * a parameter value within the same request.
     *
     * @since 3.3.7
     * @param string $name
     * @param string $value
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
     * @return boolean
     */
    public function hasParam(string $name) : bool
    {
        $value = $this->getParam($name);
        if ($value !== null) {
            return true;
        }
        
        return false;
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
     * @return bool
     */
    public function getBool($name, $default=false)
    {
        $value = $this->getParam($name, $default);
        if(ConvertHelper::isBoolean($value)) {
            return ConvertHelper::string2bool($value);
        }
        
        return false;
    }
    
    public function validate()
    {
        foreach($this->knownParams as $param) {
            $name = $param->getName();
            if($param->isRequired() && !$this->hasParam($name)) {
                throw new Request_Exception(
                    'Missing request parameter '.$name,
                    sprintf(
                        'The request parameter [%s] is required, and is either empty or invalid according to the specified format [%s].',
                        $name,
                        $param->getValidationType()
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
     * @return string
     */
    public function getFilteredParam($name, $default=null)
    {
        $val = $this->getParam($name, $default);
        if(is_string($val)) {
            $val = htmlspecialchars(trim(strip_tags($val)), ENT_QUOTES, 'UTF-8');
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
     * @return array|NULL
     */
    public function getJSON(string $name, bool $assoc=true) :?array
    {
        $value = $this->getParam($name);
        if(!empty($value)) {
            $data = json_decode($value, $assoc);
            if($data !== false) {
                return $data;
            }
        }
        
        return null;
    }
    
    /**
     * Sends a JSON response with the correct headers.
     *
     * @param array|string $data
     */
    public static function sendJSON($data)
    {
        $payload = $data;
        if(!is_string($payload)) {
            $payload = json_encode($payload);
        }
        
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        echo $payload;
        exit;
    }
    
    public static function sendHTML($html)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
    
   /**
    * Creates a new instance of the URL comparer, which can check 
    * whether the specified URLs match, regardless of the order in 
    * which the query parameters are, if any.
    * 
    * @param string $sourceURL
    * @param string $targetURL
    * @param array $limitParams Whether to limit the comparison to these specific parameter names (if present)
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