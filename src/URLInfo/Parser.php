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
 * Handles the URL parsing.
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
     * @var array
     */
    protected $knownSchemes = array(
        'ftp',
        'http',
        'https',
        'mailto',
        'tel',
        'data',
        'file'
    );
    
    public function __construct(string $url)
    {
        $this->url = $url;
        
        $this->parse();
        
        if(!$this->detectType()) {
            $this->validate();
        }
    }

    public function getInfo() : array
    {
        return $this->info;
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
    }
    
    protected function detectType() : bool
    {
        $types = array(
            'email',
            'fragmentLink',
            'phoneLink'
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
        // have been recognized as a vaiable only link.
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
    protected function filterParsed()
    {
        $this->info['params'] = array();
        $this->info['type'] = URLInfo::TYPE_URL;
        
        if(isset($this->info['user'])) {
            $this->info['user'] = urldecode($this->info['user']);
        }

        if(isset($this->info['pass'])) {
            $this->info['pass'] = urldecode($this->info['pass']);
        }
        
        if(isset($this->info['host'])) {
            $this->info['host'] = str_replace(' ', '', $this->info['host']);
        }
        
        if(isset($this->info['path'])) {
            $this->info['path'] = str_replace(' ', '', $this->info['path']);
        }
        
        if(isset($this->info['query']) && !empty($this->info['query']))
        {
            $this->info['params'] = \AppUtils\ConvertHelper::parseQueryString($this->info['query']);
            ksort($this->info['params']);
        }
    }
    
    protected function detectType_email() : bool
    {
        if(isset($this->info['scheme']) && $this->info['scheme'] == 'mailto') {
            $this->info['type'] = URLInfo::TYPE_EMAIL;
            return true;
        }
        
        if(isset($this->info['path']) && preg_match(\AppUtils\RegexHelper::REGEX_EMAIL, $this->info['path']))
        {
            $this->info['scheme'] = 'mailto';
            $this->info['type'] = URLInfo::TYPE_EMAIL;
            return true;
        }
        
        return false;
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
    
    public function isValid() : bool
    {
        return $this->isValid;
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
}
