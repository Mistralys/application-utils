<?php
/**
 * File containing the class {@see \AppUtils\Request_URLComparer}.
 * 
 * @package Application Utils
 * @subpackage Request
 * @see \AppUtils\Request_URLComparer
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * URL comparison class: used to check if URLs match
 * independently of the order of parameters, or fragments
 * and the like. Allows specifying parameters that should
 * be excluded from the comparison as well.
 * 
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_URLComparer
{
   /**
    * @var Request
    */
    protected $request;
    
   /**
    * @var string
    */
    protected $sourceURL;
    
   /**
    * @var string
    */
    protected $targetURL;
    
   /**
    * @var array
    */
    protected $limitParams = array();
    
   /**
    * @var bool
    */
    protected $isMatch = false;
    
   /**
    * @var bool
    */
    protected $ignoreFragment = true;

   /**
    * @var URLInfo
    */
    protected $sourceInfo;
    
   /**
    * @var URLInfo
    */
    protected $targetInfo;
    
    public function __construct(Request $request, string $sourceURL, string $targetURL)
    {
        $this->request = $request;
        $this->sourceURL = $sourceURL;
        $this->targetURL = $targetURL;
    }
    
    public function isMatch() : bool
    {
        $this->init();
        
        return $this->isMatch;
    }
    
    public function addLimitParam(string $name) : Request_URLComparer
    {
        if(!in_array($name, $this->limitParams)) {
            $this->limitParams[] = $name;
        }
        
        return $this;
    }
    
    public function addLimitParams(array $names) : Request_URLComparer
    {
        foreach($names as $name) {
            $this->addLimitParam($name);
        }
        
        return $this;
    }
    
    public function setIgnoreFragment(bool $ignore=true) : Request_URLComparer
    {
        $this->ignoreFragment = $ignore;
        return $this;
    }
    
    protected function init()
    {
        // so they are always in the same order. 
        sort($this->limitParams);
        
        $this->isMatch = $this->compare();
    }
    
    protected function compare()
    {
        $this->sourceInfo = parseURL($this->sourceURL);
        $this->targetInfo = parseURL($this->targetURL);
        
        $keys = array(
            'scheme',
            'host',
            'path',
            'query' 
        );
        
        if(!$this->ignoreFragment) {
            $keys[] = 'fragment';
        }
        
        foreach($keys as $key)
        {
            if(!$this->compareKey($key)) {
                return false;
            }
        }
        
        return true;
    }
    
    protected function compareKey(string $key) : bool
    {
        $sVal = $this->sourceInfo[$key];
        $tVal = $this->targetInfo[$key];
        
        $filter = 'filter_'.$key;
        
        if(method_exists($this, $filter)) 
        {
            $sVal = $this->$filter($sVal);
            $tVal = $this->$filter($tVal);
        }
        
        return $sVal === $tVal;
    }
    
    protected function filter_path(string $path) : string
    {
        // fix double slashes in URLs
        while(stristr($path, '//')) {
            $path = str_replace('//', '/', $path);
        }
        
        return ltrim($path, '/');
    }
    
    protected function filter_query(string $query) : string
    {
        if(empty($query)) {
            return '';
        }
        
        $params = ConvertHelper::parseQueryString($query);
        
        $params = $this->limitParams($params);
        
        ksort($params);
        
        return serialize($params);
    }
    
    protected function limitParams(array $params) : array
    {
        if(empty($this->limitParams)) {
            return $params;
        }
        
        $keep = array();
        
        foreach($this->limitParams as $name)
        {
            if(isset($params[$name])) {
                $keep[$name] = $params[$name];
            }
        }
        
        return $keep;
    }
}
