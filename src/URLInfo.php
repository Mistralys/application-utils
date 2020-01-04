<?php
/**
 * File containing the {@see AppUtils\URLInfo} class.
 * 
 * @package Application Utils
 * @subpackage URLInfo
 * @see AppUtils\URLInfo
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Replacement for PHP's native `parse_url` function, which
 * handles some common pitfalls and issues that are hard to 
 * follow, as well as adding a number of utility methods.
 * 
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLInfo implements \ArrayAccess
{
    const ERROR_MISSING_SCHEME = 42101;
    
    const ERROR_INVALID_SCHEME = 42102;

    const ERROR_MISSING_HOST = 42103;
    
    const ERROR_CANNOT_FIND_CSS_FOLDER = 42104;
    
    const ERROR_UNKNOWN_TYPE_FOR_LABEL = 42105;
    
    const TYPE_EMAIL = 'email';
    const TYPE_FRAGMENT = 'fragment';
    const TYPE_PHONE = 'phone';
    const TYPE_URL = 'url';
    
   /**
    * The original URL that was passed to the constructor.
    * @var string
    */
    protected $rawURL;

   /**
    * @var array
    */
    protected $info;
    
   /**
    * @var string[]
    */
    protected $excludedParams = array();
    
   /**
    * @var bool
    * @see URLInfo::setParamExclusion()
    */
    protected $paramExclusion = false;
    
   /**
    * @var array
    * @see URLInfo::getTypeLabel()
    */
    protected static $typeLabels;
    
   /**
    * @var bool
    */
    protected $highlightExcluded = false;
    
   /**
    * @var array
    */
    protected $infoKeys = array(
        'scheme',
        'host',
        'port',
        'user',
        'pass',
        'path',
        'query',
        'fragment'
    );
    
   /**
    * @var string
    */
    protected $url;
    
   /**
    * @var URLInfo_Parser
    */
    protected $parser;
    
    public function __construct(string $url)
    {
        $this->rawURL = $url;
        $this->url = self::filterURL($url);
        
        $this->parser = new URLInfo_Parser($this->url);
        $this->info = $this->parser->getInfo();
    }
    
   /**
    * Filters an URL: removes control characters and the
    * like to have a clean URL to work with.
    * 
    * @param string $url
    * @return string
    */
    public static function filterURL(string $url)
    {
        return URLInfo_Filter::filter($url);
    }
    
    /**
     * Checks if it is an https link.
     * @return boolean
     */
    public function isSecure() : bool
    {
        if(isset($this->info['scheme']) && $this->info['scheme'] === 'https') {
            return true;
        }
        
        return false;
    }
    
    public function isAnchor() : bool
    {
        return $this->info['type'] === self::TYPE_FRAGMENT;
    }
    
    public function isEmail() : bool
    {
        return $this->info['type'] === self::TYPE_EMAIL;
    }
    
    public function isPhoneNumber() : bool
    {
        return $this->info['type'] === self::TYPE_PHONE;
    }
    
   /**
    * Whether the URL is a regular URL, not one of the 
    * other types like a phone number or email address.
    * 
    * @return bool
    */
    public function isURL() : bool
    {
        $host = $this->getHost();
        return !empty($host);
    }
    
    public function isValid() : bool
    {
        return $this->parser->isValid();
    }
    
   /**
    * Retrieves the host name, or an empty string if none is present.
    * 
    * @return string
    */
    public function getHost() : string
    {
        return $this->getInfoKey('host');
    }
    
   /**
    * Retrieves the path, or an empty string if none is present.
    * @return string
    */
    public function getPath() : string
    {
        return $this->getInfoKey('path');
    }
    
    public function getFragment() : string
    {
        return $this->getInfoKey('fragment');
    }
    
    public function getScheme() : string
    {
        return $this->getInfoKey('scheme');
    }
    
   /**
    * Retrieves the port specified in the URL, or -1 if none is preseent.
    * @return int
    */
    public function getPort() : int
    {
        $port = $this->getInfoKey('port');
        if(!empty($port)) {
            return (int)$port;
        }
        
        return -1;
    }
    
   /**
    * Retrieves the raw query string, or an empty string if none is present.
    * 
    * @return string
    * 
    * @see URLInfo::getParams()
    */
    public function getQuery() : string
    {
        return $this->getInfoKey('query');
    }
    
    public function getUsername() : string
    {
        return $this->getInfoKey('user');
    }
    
    public function getPassword() : string
    {
        return $this->getInfoKey('pass');
    }
    
   /**
    * Whether the URL contains a port number.
    * @return bool
    */
    public function hasPort() : bool
    {
        return $this->getPort() !== -1;
    }
    
   /**
    * Alias for the hasParams() method.
    * @return bool
    * @see URLInfo::hasParams()
    */
    public function hasQuery() : bool
    {
        return $this->hasParams();
    }
    
    public function hasHost() : bool
    {
        return $this->getHost() !== ''; 
    }
    
    public function hasPath() : bool
    {
        return $this->getPath() !== '';
    }
    
    public function hasFragment() : bool
    {
        return $this->getFragment() !== '';
    }
    
    public function hasUsername() : bool
    {
        return $this->getUsername() !== '';
    }
    
    public function hasPassword() : bool
    {
        return $this->getPassword() !== '';
    }
    
    public function hasScheme() : bool
    {
        return $this->getScheme() !== '';
    }
    
    protected function getInfoKey(string $name) : string
    {
        if(isset($this->info[$name])) {
            return (string)$this->info[$name];
        }
        
        return '';
    }
    
    public function getNormalized() : string
    {
        if(!$this->isValid()) {
            return '';
        }
        
        if($this->isAnchor())
        {
            return '#'.$this->getFragment();
        }
        else if($this->isPhoneNumber())
        {
            return 'tel://'.$this->getHost();
        }
        else if($this->isEmail())
        {
            return 'mailto:'.$this->getPath();
        }
        
        $normalized = $this->info['scheme'].'://'.$this->info['host'];
        if(isset($this->info['path'])) {
            $normalized .= $this->info['path'];
        }
        
        $params = $this->getParams();
        if(!empty($params)) {
            $normalized .= '?'.http_build_query($params);
        }
        
        if(isset($this->info['fragment'])) {
            $normalized .= '#'.$this->info['fragment'];
        }
        
        return $normalized;
    }
    
   /**
    * Creates a hash of the URL, which can be used for comparisons.
    * Since any parameters in the URL's query are sorted alphabetically,
    * the same links with a different parameter order will have the 
    * same hash.
    * 
    * @return string
    */
    public function getHash()
    {
        return \AppUtils\ConvertHelper::string2shortHash($this->getNormalized());
    }

   /**
    * Highlights the URL using HTML tags with specific highlighting
    * class names.
    * 
    * @return string Will return an empty string if the URL is not valid.
    */
    public function getHighlighted() : string
    {
        if(!$this->isValid()) {
            return '';
        }
        
        $highlighter = new URLInfo_Highlighter($this);
        
        return $highlighter->highlight();
    }
    
    public function getErrorMessage() : string
    {
        return $this->parser->getErrorMessage();
    }
    
    public function getErrorCode() : int
    {
        return $this->parser->getErrorCode();
    }
    
    public function hasParams() : bool
    {
        $params = $this->getParams();
        return !empty($params);
    }
    
    public function countParams() : int
    {
        $params = $this->getParams();
        return count($params);
    }
    
   /**
    * Retrieves all parameters specified in the url,
    * if any, as an associative array. 
    * 
    * NOTE: Ignores parameters that have been added
    * to the excluded parameters list.
    *
    * @return array
    */
    public function getParams() : array
    {
        if(!$this->paramExclusion || empty($this->excludedParams)) {
            return $this->info['params'];
        }
        
        $keep = array();
        foreach($this->info['params'] as $name => $value) {
            if(!isset($this->excludedParams[$name])) {
                $keep[$name] = $value;
            }
        }
        
        return $keep;
    }
    
   /**
    * Retrieves the names of all parameters present in the URL, if any.
    * @return string[]
    */
    public function getParamNames() : array
    {
        $params = $this->getParams();
        return array_keys($params);
    }
    
   /**
    * Retrieves a specific parameter value from the URL.
    * 
    * @param string $name
    * @return string The parameter value, or an empty string if it does not exist.
    */
    public function getParam(string $name) : string
    {
        if(isset($this->info['params'][$name])) {
            return $this->info['params'][$name];
        }
        
        return '';
    }
    
   /**
    * Excludes an URL parameter entirely if present:
    * the parser will act as if the parameter was not
    * even present in the source URL, effectively
    * stripping it.
    *
    * @param string $name
    * @param string $reason A human readable explanation why this is excluded - used when highlighting links.
    * @return URLInfo
    */
    public function excludeParam(string $name, string $reason) : URLInfo
    {
        if(!isset($this->excludedParams[$name]))
        {
            $this->excludedParams[$name] = $reason;
            $this->setParamExclusion();
        }
        
        return $this;
    }

    /**
     * Retrieves a string identifier of the type of URL that was detected.
     *
     * @return string
     *
     * @see URLInfo::TYPE_EMAIL
     * @see URLInfo::TYPE_FRAGMENT
     * @see URLInfo::TYPE_PHONE
     * @see URLInfo::TYPE_URL
     */
    public function getType() : string
    {
        return $this->info['type'];
    }
    
    public function getTypeLabel() : string
    {
        if(!isset(self::$typeLabels))
        {
            self::$typeLabels = array(
                self::TYPE_EMAIL => t('Email'),
                self::TYPE_FRAGMENT => t('Jump mark'),
                self::TYPE_PHONE => t('Phone number'),
                self::TYPE_URL => t('URL'),
            );
        }
        
        $type = $this->getType();
        
        if(!isset(self::$typeLabels[$type]))
        {
            throw new BaseException(
                sprintf('Unknown URL type label for type [%s].', $type),
                null,
                self::ERROR_UNKNOWN_TYPE_FOR_LABEL
            );
        }
        
        return self::$typeLabels[$this->getType()];
    }

   /**
    * Whether excluded parameters should be highlighted in
    * a different color in the URL when using the
    * {@link URLInfo::getHighlighted()} method.
    *
    * @param bool $highlight
    * @return URLInfo
    */
    public function setHighlightExcluded(bool $highlight=true) : URLInfo
    {
        $this->highlightExcluded = $highlight;
        return $this;
    }
    
   /**
    * Returns an array with all relevant URL information.
    * 
    * @return array
    */
    public function toArray() : array
    {
        return array(
            'hasParams' => $this->hasParams(),
            'params' => $this->getParams(),
            'type' => $this->getType(),
            'typeLabel' => $this->getTypeLabel(),
            'normalized' => $this->getNormalized(),
            'highlighted' => $this->getHighlighted(),
            'hash' => $this->getHash(),
            'host' => $this->getHost(),
            'isValid' => $this->isValid(),
            'isURL' => $this->isURL(),
            'isEmail' => $this->isEmail(),
            'isAnchor' => $this->isAnchor(),
            'isPhoneNumber' => $this->isPhoneNumber(),
            'errorMessage' => $this->getErrorMessage(),
            'errorCode' => $this->getErrorCode(),
            'excludedParams' => array_keys($this->excludedParams)
        );
    }
    
    /**
     * Enable or disable parameter exclusion: if any parameters
     * to exclude have been added, this allows switching between
     * both modes. When enabled, methods like getNormalized or
     * getHighlighted will exclude any parameters to exclude. When
     * disabled, it will act as usual.
     *
     * This allows adding parameters to exclude, but still have
     * access to the original URLs.
     *
     * @param bool $enabled
     * @return URLInfo
     * @see URLInfo::isParamExclusionEnabled()
     * @see URLInfo::setHighlightExcluded()
     */
    public function setParamExclusion(bool $enabled=true) : URLInfo
    {
        $this->paramExclusion = $enabled;
        return $this;
    }
    
   /**
    * Whether the parameter exclusion mode is enabled:
    * In this case, if any parameters have been added to the
    * exclusion list, all relevant methods will exclude these.
    *
    * @return bool
    */
    public function isParamExclusionEnabled() : bool
    {
        return $this->paramExclusion;
    }
    
   /**
    * Checks whether the link contains any parameters that
    * are on the list of excluded parameters.
    *
    * @return bool
    */
    public function containsExcludedParams() : bool
    {
        if(empty($this->excludedParams)) {
            return false;
        }
        
        $names = array_keys($this->info['params']);
        foreach($names as $name) {
            if(isset($this->excludedParams[$name])) {
                return true;
            }
        }
        
        return false;
    }
    
    public function hasParam(string $name) : bool
    {
        $names = $this->getParamNames();
        return in_array($name, $names);
    }

    public function offsetSet($offset, $value) 
    {
        if(in_array($offset, $this->infoKeys)) {
            $this->info[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) 
    {
        return isset($this->info[$offset]);
    }
    
    public function offsetUnset($offset) 
    {
        unset($this->info[$offset]);
    }
    
    public function offsetGet($offset) 
    {
        if($offset === 'port') {
            return $this->getPort();
        }
        
        if(in_array($offset, $this->infoKeys)) {
            return $this->getInfoKey($offset);
        }
        
        return '';
    }
    
    public static function getHighlightCSS() : string
    {
        $cssFolder = realpath(__DIR__.'/../css');
        
        if($cssFolder === false) {
            throw new BaseException(
                'Cannot find package CSS folder.',
                null,
                self::ERROR_CANNOT_FIND_CSS_FOLDER
            );
        }
        
        return FileHelper::readContents($cssFolder.'/urlinfo-highlight.css');
    }
    
    public function getExcludedParams() : array
    {
        return $this->excludedParams;
    }
    
    public function isHighlightExcludeEnabled() : bool
    {
        return $this->highlightExcluded;
    }
}
