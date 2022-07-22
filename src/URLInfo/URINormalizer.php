<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\URINormalizer
 */

declare(strict_types=1);

namespace AppUtils\URLInfo;

use AppUtils\URLInfo;

/**
 * Handles normalizing a URL.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URINormalizer
{
    protected URLInfo $info;
    protected bool $auth = true;
    
    public function __construct(URLInfo $info)
    {
        $this->info = $info;
    }
    
   /**
    * Enables the authentication information in the URL,
    * if a username and password are present.
    * 
    * @param bool $enable Whether to turn it on or off.
    * @return URINormalizer
    */
    public function enableAuth(bool $enable=true) : URINormalizer
    {
        $this->auth = $enable;
        return $this;
    }
    
   /**
    * Retrieves the normalized URL.
    * @return string
    */
    public function normalize() : string
    {
        $method = 'normalize_'.$this->info->getType();
        
        return (string)$this->$method();
    }
    
    protected function normalize_fragment() : string
    {
        return '#'.$this->info->getFragment();
    }
    
    protected function normalize_phone() : string
    {
        return 'tel:'.$this->info->getHost();
    }
    
    protected function normalize_email() : string
    {
        return 'mailto:'.$this->info->getPath();
    }
    
    protected function normalize_url() : string
    {
        $scheme = $this->info->getScheme();
        $normalized = '';

        if(!empty($scheme))
        {
            $normalized = $this->info->getScheme() . '://';
        }

        $normalized = $this->renderAuth($normalized);
        $normalized .= $this->info->getHost();
        $normalized = $this->renderPort($normalized);        
        $normalized = $this->renderPath($normalized);
        $normalized = $this->renderParams($normalized);
        $normalized = $this->renderFragment($normalized);
        
        return $normalized;
    }
    
    protected function renderAuth(string $normalized) : string
    {
        if(!$this->auth || !$this->info->hasUsername()) {
            return $normalized;
        }
         
        return $normalized . urlencode($this->info->getUsername()).':'.urlencode($this->info->getPassword()).'@';
    }
    
    protected function renderPort(string $normalized) : string
    {
        if(!$this->info->hasPort()) {
            return $normalized;
        }
        
        return $normalized . ':'.$this->info->getPort();
    }
    
    protected function renderPath(string $normalized) : string
    {
        if(!$this->info->hasPath()) {
            return $normalized; 
        }
        
        return $normalized . $this->info->getPath();
    }
    
    protected function renderParams(string $normalized) : string
    {
        $params = $this->info->getParams();
        
        if(empty($params)) {
            return $normalized;
        }
        
        return $normalized . '?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
    
    protected function renderFragment(string $normalized) : string
    {
        if(!$this->info->hasFragment()) {
            return $normalized;
        }
        
        return $normalized . '#'.$this->info->getFragment();
    }
}
