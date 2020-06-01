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
class ConvertHelper_URLFinder
{
   /**
    * @var string
    */
    protected $subject;
    
   /**
    * @var boolean
    */
    protected $sorting = false;
    
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
    
   /**
    * Whether to enable sorting the URLs alphabetically (disabled by default).
    * 
    * @param bool $enabled
    * @return ConvertHelper_URLFinder
    */
    public function enableSorting(bool $enabled=true) : ConvertHelper_URLFinder
    {
        $this->sorting = $enabled;
        
        return $this;
    }
    
   /**
    * Prepares the subject string by adding a newline before all URL schemes,
    * to make it possible to parse even lists of links separated by commas or
    * the like (http://domain.com,http://domain2.com).
    */
    protected function prepareSubject() : void
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
    * 
    * @see https://gist.github.com/gruber/249502
    */
    public function getURLs() : array
    {
        $this->prepareSubject();
        
        $matches = array();
        preg_match_all('#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#i', $this->subject, $matches, PREG_PATTERN_ORDER);
        
        $result = array();
        
        if(is_array($matches))
        {
            foreach($matches[0] as $match)
            {
                if(strstr($match, '://') && !in_array($match, $result))
                {
                    $result[] = $match;
                }
            }
        }
        
        if($this->sorting)
        {
            usort($result, function(string $a, string $b) {
                return strnatcasecmp($a, $b);
            });
        }
        
        return $result;
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
