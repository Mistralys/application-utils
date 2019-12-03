<?php

declare(strict_types=1);

namespace AppUtils;

class URLInfo
{
    const ERROR_MISSING_SCHEME = 42101;
    
    const ERROR_INVALID_SCHEME = 42102;

    const ERROR_MISSING_HOST = 42103;
    
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
    * @var string
    */
    protected $info;
    
    protected $isEmail = false;
    
    protected $isFragment = false;
    
    protected $isValid = true;
    
    protected $isPhone = false;
    
   /**
    * @var string[]
    */
    protected $knownSchemes = array(
        'ftp',
        'http',
        'https',
        'mailto',
        'tel'
    );

   /**
    * @var array
    */
    protected $error;
    
   /**
    * @var array
    */
    protected $params = array();
    
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
    
    public function __construct(string $url)
    {
        $this->rawURL = $url;
        $this->url = self::filterURL($url);
        
        $this->parse();
    }
    
    protected function parse()
    {
        // fix for parsing unicode characters in URLs:
        // this is dependent on the machine's locale,
        // so to ensure this works we temporarily change
        // it to the always available US UTF8 locale.
        $prev = setlocale(LC_CTYPE, 'en_US.UTF-8');
        
        $this->info = parse_url($this->url);
        
        // restore the previous locale
        setlocale(LC_CTYPE, $prev);

        $this->filterParsed();
        
        if($this->detectEmail()) {
            return;
        }
        
        if($this->detectFragmentLink()) {
            return;
        }
        
        if($this->detectPhoneLink()) {
            return;
        }
        
        if(!$this->isValid) {
            return;
        }
        
        // no scheme found: it may be an email address without the mailto:
        // It can't be a variable, since without the scheme it would already
        // have been recognized as a vaiable only link.
        if(!isset($this->info['scheme'])) {
            $this->setError(
                self::ERROR_MISSING_SCHEME,
                t('Cannot determine the link\'s scheme, e.g. %1$s.', 'http')
            );
            $this->isValid = false;
            return;
        }
        
        if(!in_array($this->info['scheme'], $this->knownSchemes)) {
            $this->setError(
                self::ERROR_INVALID_SCHEME,
                t('The scheme %1$s is not supported for links.', $this->info['scheme']) . ' ' .
                t('Valid schemes are: %1$s.', implode(', ', $this->knownSchemes))
            );
            $this->isValid = false;
            return;
        }
        
        // every link needs a host. This case can happen for ex, if
        // the link starts with a typo with only one slash, like:
        // "http:/hostname"
        if(!isset($this->info['host'])) {
            $this->setError(
                self::ERROR_MISSING_HOST,
                t('Cannot determine the link\'s host name.') . ' ' .
                t('This usually happens when there\'s a typo somewhere.')
            );
            $this->isValid = false;
            return;
        }

        if(!empty($this->info['query'])) 
        {
            $this->params = \AppUtils\ConvertHelper::parseQueryString($this->info['query']);
            ksort($this->params);
        }
        
        $this->isValid = true;
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
        // fix ampersands if it comes from HTML
        $url = str_replace('&amp;', '&', $url);
        
        // we remove any control characters from the URL, since these
        // may be copied when copy+pasting from word or pdf documents
        // for example.
        $url = \AppUtils\ConvertHelper::stripControlCharacters($url);
        
        // fix the pesky unicode hyphen that looks like a regular hyphen,
        // but isn't and can cause all sorts of problems
        $url = str_replace('‐', '-', $url);
        
        // remove newlines and tabs
        $url = str_replace(array("\n", "\r", "\t"), '', $url);
        
        $url = trim($url);
        
        return $url;
    }
    
   /**
    * Goes through all information in the parse_url result
    * array, and attempts to fix any user errors in formatting
    * that can be recovered from, mostly regarging stray spaces.
    */
    protected function filterParsed()
    {
        foreach($this->info as $key => $val)
        {
            $this->info[$key] = trim($val);
        }
        
        if(isset($this->info['scheme'])) {
            $this->info['scheme'] = trim($this->info['scheme']);
        }
        
        if(isset($this->info['host'])) {
            $this->info['host'] = str_replace(' ', '', $this->info['host']);
        }
        
        if(isset($this->info['path'])) {
            $this->info['path'] = str_replace(' ', '', $this->info['path']);
        }
    }
    
    protected function detectEmail()
    {
        if(isset($this->info['scheme']) && $this->info['scheme'] == 'mailto') {
            $this->isEmail = true;
            return true;
        }
        
        if(isset($this->info['path']) && preg_match(\AppUtils\RegexHelper::REGEX_EMAIL, $this->info['path'])) 
        {
            $this->info['scheme'] = 'email';
            $this->isEmail = true;
            return true;
        }
        
        return false;
    }
    
    protected function detectFragmentLink()
    {
        if(isset($this->info['fragment']) && !isset($this->info['scheme'])) {
            $this->isFragment = true;
            return true;
        }
        
        return false;
    }
    
    protected function detectPhoneLink()
    {
        if(isset($this->info['scheme']) && $this->info['scheme'] == 'tel') {
            $this->isPhone = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * Checks if it is an https link.
     * @return boolean
     */
    public function isSecure()
    {
        if(isset($this->info['scheme']) && $this->info['scheme']=='https') {
            return true;
        }
        
        return false;
    }
    
    public function isAnchor() : bool
    {
        return $this->isFragment;
    }
    
    public function isEmail() : bool
    {
        return $this->isEmail;
    }
    
    public function isPhoneNumber() : bool
    {
        return $this->isPhone;
    }
    
    public function isURL() : bool
    {
        return isset($this->info['host']);
    }
    
    public function isValid()
    {
        return $this->isValid;
    }
    
    public function getHost() : string
    {
        if(isset($this->info['host'])) {
            return $this->info['host'];
        }
        
        return '';
    }
    
    public function getNormalized() : string
    {
        if(!$this->isValid) {
            return '';
        }
        
        if($this->isFragment === true)
        {
            return '#'.$this->info['fragment'];
        }
        else if($this->isPhone === true)
        {
            return 'tel://'.$this->info['host'];
        }
        else if($this->isEmail === true)
        {
            return 'mailto:'.$this->info['path'];
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
    
    public function getHash()
    {
        return \AppUtils\ConvertHelper::string2shortHash($this->getNormalized());
    }

    public function getHighlighted() : string
    {
        if(!$this->isValid) {
            return '';
        }
        
        if($this->isEmail) {
            return sprintf(
                '<span class="link-scheme-mailto">mailto:</span>'.
                '<span class="link-host">%s</span>',
                $this->replaceVars($this->info['path'])
            );
        }
        
        if($this->isFragment) {
            return sprintf(
                '<span class="link-fragment-sign">#</span>'.
                '<span class="link-fragment">%s</span>',
                $this->replaceVars($this->info['fragment'])
            );
        }
        
        $result = sprintf(
            '<span class="link-scheme-%s">'.
            '%s://'.
            '</span>',
            $this->info['scheme'],
            $this->info['scheme']
        );
        
        if(isset($this->info['host'])) {
            $result .=
            sprintf(
                '<span class="link-host">%s</span><wbr>',
                $this->info['host']
            );
        }
        
        if(isset($this->info['path'])) 
        {
            $path = str_replace(array(';', '='), array(';<wbr>', '=<wbr>'), $this->info['path']);
            $tokens = explode('/', $path);
            $path = implode('<span class="link-component">/</span><wbr>', $tokens);
            $result .= sprintf(
                '<span class="link-path">%s</span><wbr>',
                $this->replaceVars($path)
            );
        }
        
        if(!empty($this->params))
        {
            $tokens = array();
            
            foreach($this->params as $param => $value)
            {
                $parts = sprintf(
                    '<span class="link-param-name">%s</span>'.
                    '<span class="link-equals-sign">=</span>'.
                    '<span class="link-param-value">%s</span>'.
                    '<wbr>',
                    $param,
                    str_replace(
                        array(':', '.', '-', '_'),
                        array(':<wbr>', '.<wbr>', '-<wbr>', '_<wbr>'),
                        $value
                    )
                );
                
                $tag = '';
                
                // is parameter exclusion enabled, and is this an excluded parameter?
                if($this->paramExclusion && isset($this->excludedParams[$param]))
                {
                    // display the excluded parameter, but highlight it
                    if($this->highlightExcluded)
                    {
                        $tooltip = $this->excludedParams[$param];
                        
                        $tag = sprintf(
                            '<span class="link-param excluded-param" title="%s" data-toggle="tooltip">%s</span>',
                            $tooltip,
                            $parts
                        );
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    $tag = sprintf(
                        '<span class="link-param">%s</span>',
                        $parts
                    );
                }
                
                $tokens[] = $tag;
            }
            
            $result .=
            '<span class="link-query-sign">?</span>'.implode('<span class="link-component">&amp;</span>', $tokens);
        }
        
        if(isset($this->info['fragment'])) {
            $result .= sprintf(
                '<span class="link-fragment-sign">#</span>'.
                '<span class="link-fragment">%s</span>',
                $this->info['fragment']
            );
        }
        
        $result = '<span class="link">'.$result.'</span>';
        
        return $result;
    }
    
    protected function setError(int $code, string $message)
    {
        $this->error = array(
            'code' => $code,
            'message' => $message
        );
    }
    
    public function getErrorMessage() : string
    {
        if(isset($this->error)) {
            return $this->error['message'];
        }
        
        return '';
    }
    
    public function getErrorCode() : int
    {
        if(isset($this->error)) {
            return $this->error['code'];
        }
        
        return -1;
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
            return $this->params;
        }
        
        $keep = array();
        foreach($this->params as $name => $value) {
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
        if($this->isEmail) {
            return self::TYPE_EMAIL;
        }
        
        if($this->isFragment) {
            return self::TYPE_FRAGMENT;
        }
        
        if($this->isPhone) {
            return self::TYPE_PHONE;
        }
        
        return self::TYPE_URL;
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
        
        $names = array_keys($this->params);
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
}
