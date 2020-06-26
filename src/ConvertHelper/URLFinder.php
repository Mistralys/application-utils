<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_URLFinder} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_URLFinder
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Can find any URLs in a string, be it plain text or HTML, XML.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see ConvertHelper::createURLFinder()
 */
class ConvertHelper_URLFinder implements Interface_Optionable
{
    use Traits_Optionable;
    
   /**
    * @see https://gist.github.com/gruber/249502
    */
    const REGEX_URL = '#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#i';
    
   /**
    * @var string
    */
    protected $subject;

   /**
    * @var string[]
    */
    protected $schemes = array(
        'http',
        'https',
        'ftp',
        'ftps',
        'mailto',
        'svn',
        'ssl',
        'tel',
    );
    
    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'include-emails' => false,
            'omit-mailto' => false,
            'sorting' => false
        );
    }
    
   /**
    * Whether to enable sorting the URLs alphabetically (disabled by default).
    * 
    * @param bool $enabled
    * @return ConvertHelper_URLFinder
    */
    public function enableSorting(bool $enabled=true) : ConvertHelper_URLFinder
    {
        $this->setOption('sorting', $enabled);
        return $this;
    }
    
   /**
    * Whether to include email addresses in the search. 
    * This is only relevant when using the getURLs()
    * method.
    * 
    * @param bool $include
    * @return ConvertHelper_URLFinder
    */
    public function includeEmails(bool $include=true) : ConvertHelper_URLFinder
    {
        $this->setOption('include-emails', $include);
        return $this;
    }
    
   /**
    * Whether to omit the mailto: that is automatically added to all email addresses.
    * 
    * @param bool $omit
    * @return ConvertHelper_URLFinder
    */
    public function omitMailto(bool $omit=true) : ConvertHelper_URLFinder
    {
        $this->setOption('omit-mailto', $omit);
        return $this;
    }
    
   /**
    * Prepares the subject string by adding a newline before all URL schemes,
    * to make it possible to parse even lists of links separated by commas or
    * the like (http://domain.com,http://domain2.com).
    */
    private function prepareSubject() : void
    {
        $replaces = array();
        
        foreach($this->schemes as $scheme)
        {
            $replaces[$scheme.':'] = PHP_EOL.$scheme.':';
        }
        
        $this->subject = str_replace(array_keys($replaces), array_values($replaces), $this->subject);
    }
    
   /**
    * Fetches all URLs that can be found in the subject string.
    * 
    * @return string[]
    */
    public function getURLs() : array
    {
        $this->prepareSubject();
        
        $matches = array();
        preg_match_all(self::REGEX_URL, $this->subject, $matches, PREG_PATTERN_ORDER);
        
        $result = array();
        
        foreach($matches[0] as $match)
        {
            if(strstr($match, '://') && !in_array($match, $result))
            {
                $result[] = $match;
            }
        }
        
        if($this->getBoolOption('include-emails'))
        {
            $result = array_merge($result, $this->getEmails());
        }
        
        if($this->getBoolOption('sorting'))
        {
            usort($result, function(string $a, string $b) {
                return strnatcasecmp($a, $b);
            });
        }
        
        return $result;
    }
    
   /**
    * Retrieves all email addresses from the subject string.
    * 
    * @return string[]
    * 
    * @see omitMailto()
    */
    public function getEmails() : array
    {
        $matches = array();
        preg_match_all(RegexHelper::REGEX_EMAIL, $this->subject, $matches, PREG_PATTERN_ORDER);
        
        $result = array();
        $prefix = $this->getEmailPrefix();
        
        foreach($matches[0] as $email)
        {
            $result[] = $prefix.$email;
        }
        
        if($this->getBoolOption('sorting'))
        {
            usort($result, function(string $a, string $b) {
                return strnatcasecmp($a, $b);
            });
        }
        
        return $result;
    }
    
    private function getEmailPrefix() : string
    {
        if($this->getBoolOption('omit-mailto'))
        {
            return '';
        }
        
        return 'mailto:';
    }
    
   /**
    * Retrieves all URLs as URLInfo instances.
    * 
    * @return URLInfo[]
    */
    public function getInfos()
    {
        $urls = $this->getURLs();
        
        $result = array();
        
        foreach($urls as $url)
        {
            $result[] = parseURL($url);
        }
        
        return $result;
    }
}
