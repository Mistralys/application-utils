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

use AppUtils\URLInfo\URIConnectionTester;
use AppUtils\URLInfo\URIFilter;
use AppUtils\URLInfo\URINormalizer;
use AppUtils\URLInfo\URIParser;
use AppUtils\URLInfo\URLException;
use ArrayAccess;

/**
 * Replacement for PHP's native `parse_url` function, which
 * handles some common pitfalls and issues that are hard to 
 * follow, as well as adding a number of utility methods.
 * 
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,mixed>
 */
class URLInfo implements ArrayAccess
{
    public const ERROR_MISSING_SCHEME = 42101;
    public const ERROR_INVALID_SCHEME = 42102;
    public const ERROR_MISSING_HOST = 42103;
    public const ERROR_CANNOT_FIND_CSS_FOLDER = 42104;
    public const ERROR_UNKNOWN_TYPE_FOR_LABEL = 42105;
    public const ERROR_CURL_INIT_FAILED = 42106;
    public const ERROR_UNKNOWN_TYPE = 42107;
    
    public const TYPE_EMAIL = 'email';
    public const TYPE_FRAGMENT = 'fragment';
    public const TYPE_PHONE = 'phone';
    public const TYPE_URL = 'url';
    public const TYPE_NONE = 'none';

   /**
    * The original URL that was passed to the constructor.
    * @var string
    */
    protected string $rawURL;

   /**
    * @var array<string,mixed>
    */
    protected array $info;
    
   /**
    * @var string[]
    */
    protected array $excludedParams = array();
    
   /**
    * @var bool
    * @see URLInfo::setParamExclusion()
    */
    protected bool $paramExclusion = false;
    
   /**
    * @var array<string,string>|NULL
    * @see URLInfo::getTypeLabel()
    */
    protected static ?array $typeLabels = null;
    
   /**
    * @var bool
    */
    protected bool $highlightExcluded = false;
    
   /**
    * @var string[]
    */
    protected array $infoKeys = array(
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
    protected string $url;
    
   /**
    * @var URIParser
    */
    protected URIParser $parser;
    
   /**
    * @var URINormalizer|NULL
    */
    protected ?URINormalizer $normalizer = null;
    
   /**
    * @var bool
    */
    protected bool $encodeUTFChars = false;
    
    public function __construct(string $url)
    {
        $this->rawURL = $url;
        $this->url = self::filterURL($url);
        
        $this->parse();
    }
    
    protected function parse() : void
    {
        $this->parser = new URIParser($this->url, $this->encodeUTFChars);
        $this->info = $this->parser->getInfo();
    }

   /**
    * Whether to URL encode any non-encoded UTF8 characters in the URL.
    * Default is to leave them as-is for better readability, since 
    * browsers handle this well.
    * 
    * @param bool $enabled
    * @return URLInfo
    */
    public function setUTFEncoding(bool $enabled=true) : URLInfo
    {
        if($this->encodeUTFChars !== $enabled)
        {
            $this->encodeUTFChars = $enabled;
            $this->parse(); // reparse the URL to apply the changes
        }
        
        return $this;
    }
    
    public function isUTFEncodingEnabled() : bool
    {
        return $this->encodeUTFChars;
    }
    
   /**
    * Filters a URL: removes control characters and the
    * like to have a clean URL to work with.
    * 
    * @param string $url
    * @return string
    */
    public static function filterURL(string $url) : string
    {
        return URIFilter::filter($url);
    }
    
    /**
     * Checks if it is a https link.
     * @return boolean
     */
    public function isSecure() : bool
    {
        return $this->getScheme() === 'https';
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
    * Retrieves the port specified in the URL, or -1 if none is present.
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

   /**
    * Retrieves a normalized URL: this ensures that all parameters
    * in the URL are always in the same order.
    * 
    * @return string
    */
    public function getNormalized() : string
    {
        return $this->normalize();
    }
    
   /**
    * Like getNormalized(), but if a username and password are present
    * in the URL, returns the URL without them.
    * 
    * @return string
    */
    public function getNormalizedWithoutAuth() : string
    {
        return $this->normalize(false);
    }
    
    protected function normalize(bool $auth=true) : string
    {
        if(!$this->isValid()) {
            return '';
        }
        
        if(!isset($this->normalizer)) {
            $this->normalizer = new URINormalizer($this);
        }
        
        $this->normalizer->enableAuth($auth);
        
        return $this->normalizer->normalize();
    }
    
   /**
    * Creates a hash of the URL, which can be used for comparisons.
    * Since any parameters in the URL's query are sorted alphabetically,
    * the same links with a different parameter order will have the 
    * same hash.
    * 
    * @return string
    */
    public function getHash() : string
    {
        return ConvertHelper::string2shortHash($this->getNormalized());
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
        
        return (new URIHighlighter($this))->highlight();
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
    * @return array<string,string>
    */
    public function getParams() : array
    {
        if(!$this->paramExclusion || empty($this->excludedParams)) {
            return $this->info['params'];
        }
        
        $keep = array();
        foreach($this->info['params'] as $name => $value) 
        {
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
        return $this->info['params'][$name] ?? '';
    }
    
   /**
    * Excludes a URL parameter entirely if present:
    * the parser will act as if the parameter was not
    * even present in the source URL, effectively
    * stripping it.
    *
    * @param string $name
    * @param string $reason A human-readable explanation why this is excluded - used when highlighting links.
    * @return URLInfo
    */
    public function excludeParam(string $name, string $reason='') : URLInfo
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
            throw new URLException(
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
     * @return array<string,mixed>
     * @throws URLException
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

    public function offsetSet($offset, $value)  : void
    {
        if(in_array($offset, $this->infoKeys, true)) {
            $this->info[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) : bool
    {
        return isset($this->info[$offset]);
    }
    
    public function offsetUnset($offset) : void
    {
        unset($this->info[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if($offset === 'port') {
            return $this->getPort();
        }
        
        if(in_array($offset, $this->infoKeys, true)) {
            return $this->getInfoKey($offset);
        }
        
        return '';
    }
    
    public static function getHighlightCSS() : string
    {
        return URIHighlighter::getHighlightCSS();
    }

    /**
     * @return string[]
     */
    public function getExcludedParams() : array
    {
        return $this->excludedParams;
    }
    
    public function isHighlightExcludeEnabled() : bool
    {
        return $this->highlightExcluded;
    }

    /**
     * Checks if the URL exists, i.e. can be connected to. Will return
     * true if the returned HTTP status code is `200` or `302`.
     *
     * NOTE: If the target URL requires HTTP authentication, the username
     * and password should be integrated into the URL.
     *
     * @param bool $verifySSL
     * @return bool
     */
    public function tryConnect(bool $verifySSL=true) : bool
    {
        return $this->createConnectionTester()
            ->setVerifySSL($verifySSL)
            ->canConnect();
    }
    
   /**
    * Creates the connection tester instance that is used
    * to check if a URL can be connected to, and which is
    * used in the {@see URLInfo::tryConnect()} method. It
    * allows more settings to be used.
    * 
    * @return URIConnectionTester
    */
    public function createConnectionTester() : URIConnectionTester
    {
        return new URIConnectionTester($this);
    }
    
   /**
    * Adds/overwrites a URL parameter.
    *  
    * @param string $name
    * @param string $val
    * @return URLInfo
    */
    public function setParam(string $name, string $val) : URLInfo
    {
        $this->info['params'][$name] = $val;
        
        return $this;
    }
    
   /**
    * Removes a URL parameter. Has no effect if the
    * parameter is not present to begin with.
    * 
    * @param string $param
    * @return URLInfo
    */
    public function removeParam(string $param) : URLInfo
    {
        if(isset($this->info['params'][$param]))
        {
            unset($this->info['params'][$param]);
        }
        
        return $this;
    }

    public function hasIPAddress() : bool
    {
        return isset($this->info['ip']);
    }
}
