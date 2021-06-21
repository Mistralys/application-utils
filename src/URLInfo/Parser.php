<?php
/**
 * File containing the {@see AppUtils\URLInfo_Parser} class.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @see AppUtils\URLInfo_Parser
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Handles the URL parsing, as replacement for PHP's 
 * native parse_url function. It overcomes a number of
 * limitations of the function, using pre- and post-
 * processing of the URL and its component parts.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLInfo_Parser
{
   /**
    * @var string
    */
    protected $url;
    
   /**
    * @var bool
    */
    protected $isValid = false;
    
   /**
    * @var array
    */
    protected $info;
    
   /**
    * @var array|NULL
    */
    protected $error;
    
    /**
     * @var string[]
     */
    protected $knownSchemes = array(
        'ftp',
        'http',
        'https',
        'mailto',
        'tel',
        'data',
        'file',
        'git'
    );
    
   /**
    * Stores a list of all unicode characters in the URL
    * that have been filtered out before parsing it with
    * parse_url.
    * 
    * @var string[]string
    */
    protected $unicodeChars = array();
    
   /**
    * @var bool
    */
    protected $encodeUTF = false;
    
   /**
    * 
    * @param string $url The target URL.
    * @param bool $encodeUTF Whether to URL encode any plain text unicode characters.
    */
    public function __construct(string $url, bool $encodeUTF)
    {
        $this->url = $url;
        $this->encodeUTF = $encodeUTF;
        
        $this->parse();
        
        if(!$this->detectType()) {
            $this->validate();
        }
    }

   /**
    * Retrieves the array as parsed by PHP's parse_url,
    * filtered and adjusted as necessary.
    * 
    * @return array
    */
    public function getInfo() : array
    {
        return $this->info;
    }
    
    protected function parse()
    {
        $this->filterUnicodeChars();
        
        $this->info = parse_url($this->url);

        $this->filterParsed();
        
        // if the URL contains any URL characters, and we
        // do not want them URL encoded, restore them.
        if(!$this->encodeUTF && !empty($this->unicodeChars))
        {
            $this->info = $this->restoreUnicodeChars($this->info);
        }
    }

   /**
    * Finds any non-url encoded unicode characters in 
    * the URL, and encodes them before the URL is 
    * passed to parse_url.
    */
    protected function filterUnicodeChars() : void
    {
        $chars = ConvertHelper::string2array($this->url);
        
        $keep = array();
        
        foreach($chars as $char)
        {
            if(preg_match('/\p{L}/usix', $char))
            {
                $encoded = rawurlencode($char);
                
                if($encoded != $char)
                {
                    $this->unicodeChars[$encoded] = $char;
                    $char = $encoded;
                }
            }
            
            $keep[] = $char;
        }
        
        $this->url = implode('', $keep);
    }
    
    protected function detectType() : bool
    {
        $types = array(
            'email',
            'fragmentLink',
            'phoneLink',
            'ipAddress'
        );
        
        foreach($types as $type)
        {
            $method = 'detectType_'.$type;
            
            if($this->$method() === true) 
            {
                $this->isValid = true;
                return true;
            }
        }
        
        return false;
    }
    
    protected function validate()
    {
        $validations = array(
            'schemeIsSet',
            'schemeIsKnown',
            'hostIsPresent'
        );
        
        foreach($validations as $validation) 
        {
            $method = 'validate_'.$validation;
            
            if($this->$method() !== true) {
                return;
            }
        }
        
        $this->isValid = true;
    }
    
    protected function validate_hostIsPresent() : bool
    {
        // every link needs a host. This case can happen for ex, if
        // the link starts with a typo with only one slash, like:
        // "http:/hostname"
        if(isset($this->info['host'])) {
            return true;
        }
        
        $this->setError(
            URLInfo::ERROR_MISSING_HOST,
            t('Cannot determine the link\'s host name.') . ' ' .
            t('This usually happens when there\'s a typo somewhere.')
        );

        return false;
    }
    
    protected function validate_schemeIsSet() : bool
    {
        if(isset($this->info['scheme'])) {
            return true;
        }
        
        // no scheme found: it may be an email address without the mailto:
        // It can't be a variable, since without the scheme it would already
        // have been recognized as a variable only link.
        $this->setError(
            URLInfo::ERROR_MISSING_SCHEME,
            t('Cannot determine the link\'s scheme, e.g. %1$s.', 'http')
        );
        
        return false;
    }
    
    protected function validate_schemeIsKnown() : bool
    {
        if(in_array($this->info['scheme'], $this->knownSchemes)) {
            return true;
        }
        
        $this->setError(
            URLInfo::ERROR_INVALID_SCHEME,
            t('The scheme %1$s is not supported for links.', $this->info['scheme']) . ' ' .
            t('Valid schemes are: %1$s.', implode(', ', $this->knownSchemes))
        );

        return false;
    }

   /**
    * Goes through all information in the parse_url result
    * array, and attempts to fix any user errors in formatting
    * that can be recovered from, mostly regarding stray spaces.
    */
    protected function filterParsed() : void
    {
        $this->info['params'] = array();
        $this->info['type'] = URLInfo::TYPE_URL;

        if(isset($this->info['scheme']))
        {
            $this->info['scheme'] = strtolower($this->info['scheme']);
        }
        else
        {
            $scheme = URLInfo_Schemes::detectScheme($this->url);
            if(!empty($scheme)) {
                $this->info['scheme'] = substr($scheme,0, strpos($scheme, ':'));
            }
        }

        if(isset($this->info['user'])) {
            $this->info['user'] = urldecode($this->info['user']);
        }

        if(isset($this->info['pass'])) {
            $this->info['pass'] = urldecode($this->info['pass']);
        }
        
        if(isset($this->info['host'])) {
            $this->info['host'] = strtolower($this->info['host']);
            $this->info['host'] = str_replace(' ', '', $this->info['host']);
        }
        
        if(isset($this->info['path'])) {
            $this->info['path'] = str_replace(' ', '', $this->info['path']);
        }
        
        if(isset($this->info['query']) && !empty($this->info['query']))
        {
            $this->info['params'] = ConvertHelper::parseQueryString($this->info['query']);
            ksort($this->info['params']);
        }
    }
    
   /**
    * Recursively goes through the array, and converts all previously
    * URL encoded characters with their unicode character counterparts.
    * 
    * @param array $subject
    * @return array
    */
    protected function restoreUnicodeChars(array $subject) : array
    {
        $result = array();
        
        foreach($subject as $key => $val)
        {
            if(is_array($val))
            {
                $val = $this->restoreUnicodeChars($val);
            }
            else
            {
                $val = $this->restoreUnicodeChar($val);
            }
            
            $key = $this->restoreUnicodeChar($key);
            
            $result[$key] = $val;
        }
        
        return $result;
    }
    
   /**
    * Replaces all URL encoded unicode characters
    * in the string with the unicode character.
    * 
    * @param string $string
    * @return string
    */
    protected function restoreUnicodeChar(string $string) : string
    {
        if(strstr($string, '%'))
        {
            return str_replace(array_keys($this->unicodeChars), array_values($this->unicodeChars), $string);
        }
        
        return $string;
    }
    
    protected function detectType_email() : bool
    {
        if(isset($this->info['scheme']) && $this->info['scheme'] == 'mailto') {
            $this->info['type'] = URLInfo::TYPE_EMAIL;
            return true;
        }
        
        if(isset($this->info['path']) && preg_match(RegexHelper::REGEX_EMAIL, $this->info['path']))
        {
            $this->info['scheme'] = 'mailto';
            $this->info['type'] = URLInfo::TYPE_EMAIL;
            return true;
        }
        
        return false;
    }

    protected function detectType_ipAddress() : bool
    {
        if($this->isPathOnly() && preg_match(RegexHelper::REGEX_IPV4, $this->info['path'])) {
            $this->info['host'] = $this->info['path'];
            $this->info['scheme'] = 'https';
            unset($this->info['path']);
        }

        if($this->isHostOnly() && preg_match(RegexHelper::REGEX_IPV4, $this->info['host'])) {
            $this->info['ip'] = $this->info['host'];
            return true;
        }

        return false;
    }

    private function isPathOnly() : bool
    {
        return isset($this->info['path']) && !isset($this->info['host']) && !isset($this->info['scheme']);
    }

    private function isHostOnly() : bool
    {
        return isset($this->info['host']) && !isset($this->info['path']) && !isset($this->info['query']);
    }

    protected function detectType_fragmentLink() : bool
    {
        if(isset($this->info['fragment']) && !isset($this->info['scheme'])) {
            $this->info['type'] = URLInfo::TYPE_FRAGMENT;
            return true;
        }
        
        return false;
    }
    
    protected function detectType_phoneLink() : bool
    {
        if(isset($this->info['scheme']) && $this->info['scheme'] == 'tel') {
            $this->info['type'] = URLInfo::TYPE_PHONE;
            return true;
        }
        
        return false;
    }

    protected function setError(int $code, string $message)
    {
        $this->isValid = false;
        
        $this->error = array(
            'code' => $code,
            'message' => $message
        );
    }
   
   /**
    * Checks whether the URL that was parsed is valid.
    * @return bool
    */
    public function isValid() : bool
    {
        return $this->isValid;
    }

   /**
    * If the validation failed, retrieves the validation
    * error message.
    * 
    * @return string
    */
    public function getErrorMessage() : string
    {
        if(isset($this->error)) {
            return $this->error['message'];
        }
        
        return '';
    }
    
   /**
    * If the validation failed, retrieves the validation
    * error code.
    * 
    * @return int
    */
    public function getErrorCode() : int
    {
        if(isset($this->error)) {
            return $this->error['code'];
        }
        
        return -1;
    }
}
