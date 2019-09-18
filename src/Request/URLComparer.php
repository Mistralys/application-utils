<?php

declare(strict_types=1);

namespace AppUtils;

class Request_URLComparer
{
    protected $sourceURL;
    
    protected $targetURL;
    
    protected $limitParams = array();
    
    protected $isMatch;
    
    protected $ignoreFragment = true;
    
    public function __construct(Request $request, string $sourceURL, string $targetURL)
    {
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
        if(isset($this->isMatch)) {
            return;
        }
        
        // so they are always in the same order. 
        sort($this->limitParams);
        
        $this->isMatch = $this->compare();
    }
    
    protected function compare()
    {
        $sInfo = parse_url($this->sourceURL);
        $tInfo = parse_url($this->targetURL);
        
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
            $sVal = '';
            $tVal = '';
            
            if(isset($sInfo[$key])) { $sVal = $sInfo[$key]; }
            if(isset($tInfo[$key])) { $tVal = $tInfo[$key]; }
            
            $filter = 'filter_'.$key;
            if(method_exists($this, $filter)) {
                $sVal = $this->$filter($sVal);
                $tVal = $this->$filter($tVal);
            }
            
            if($sVal !== $tVal) {
                return false;
            }
        }
        
        return true;
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
        
        $params = null; 
        parse_str($query, $params);
        
        ksort($params);
        
        if(!empty($this->limitParams))
        {
            $keep = array();
            
            foreach($this->limitParams as $name)
            {
                if(isset($params[$name])) {
                    $keep[$name] = $params[$name];
                }
            }
            
            $params = $keep;
        }
        
        return serialize($params);
    }
}