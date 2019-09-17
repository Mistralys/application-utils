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
            $o = array();
            $o['pos'] = $i;
            $M = null;
            if (preg_match('/^(\S+)\s*;\s*(?:q|level)=([0-9\.]+)/i', $term, $M)) {
                $o['type'] = $M[1];
                $o['quality'] = (double)$M[2];
            } else {
                $o['type'] = $term;
                $o['quality'] = 1;
            }
            
            $accept[] = $o;
        }
        
        usort($accept, array('Request', 'sortAcceptHeaders'));
        
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
    public function setParam($name, $value)
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
    public function hasParam($name)
    {
        $value = $this->getParam($name);
        if ($value != null) {
            return true;
        }
        
        return false;
    }
    
    public function removeParam($name)
    {
        if(isset($_REQUEST[$name])) {
            unset($_REQUEST[$name]);
        }
    }
    
    public function removeParams($names)
    {
        foreach($names as $name) {
            $this->removeParam($name);
        }
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
        if(ConvertHelper::isBooleanString($value)) {
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
    public function getJSON($name, $assoc=true)
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
    * Checks whether the specified URLs match, regardless of
    * the order in which the query parameters are, if any.
    * 
    * @param string $sourceURL
    * @param string $targetURL
    * @param array $limitParams Wheter to limit the comparison to these specific parameter names (if present)
    * @return bool
    */
    public function urlsMatch(string $sourceURL, string $targetURL, array $limitParams=array()) : bool
    {
        $sInfo = parse_url($sourceURL);
        $tInfo = parse_url($targetURL);
        
        if($sInfo['scheme'] != $tInfo['scheme']) {
            return false;
        }
        
        if($sInfo['host'] != $tInfo['host']) {
            return false;
        }
        
        $sPath = '';
        if(isset($sInfo['path'])) {
            $sPath = ltrim($sInfo['path'], '/');
        }
        
        $tPath = '';
        if(isset($tInfo['path'])) {
            $tPath = ltrim($tInfo['path'], '/');
        }        
        
        if($sPath !== $tPath) {
            return false;
        }
        
        if($sPath && $sInfo['path'] != $tInfo['path']) {
            return false;
        }
            
        $sQuery = isset($sInfo['query']);
        $tQuery = isset($tInfo['query']);
        
        if($sQuery !== $tQuery) {
            return false;
        }
        
        if($sQuery)
        {
            $sParams = null; parse_str($sInfo['query'], $sParams);
            $tParams = null; parse_str($tInfo['query'], $tParams);
            
            ksort($sParams);
            ksort($tParams);
            
            if(!empty($limitParams)) 
            {
                $sKeep = array();
                $tKeep = array();
                
                foreach($limitParams as $name) 
                {
                    if(isset($sParams[$name])) {
                        $sKeep[$name] = $sParams[$name];
                    }
                    
                    if(isset($tParams[$name])) {
                        $tKeep[$name] = $tParams[$name];
                    }
                }
                
                $sParams = $sKeep;
                $tParams = $tKeep;
            }
            
            if(serialize($sParams) != serialize($tParams)) {
                return false;
            }
        }
        
        return true;
    }
    
   /**
    * Retrieves the full URL that was used to access the current page.
    * @return string
    */
    public function getCurrentURL() : string
    {
        return $_SERVER['REQUEST_URI'];
    }
}