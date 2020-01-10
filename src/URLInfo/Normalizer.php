<?php
/**
 * File containing the {@see AppUtils\URLInfo_Normalizer} class.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @see AppUtils\URLInfo_Normalizer
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Handles normalizing an URL.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLInfo_Normalizer
{
    /**
     * @var URLInfo
     */
    protected $info;
    
    protected $auth = true;
    
    public function __construct(URLInfo $info)
    {
        $this->info = $info;
    }
    
   /**
    * Enables the authentication information in the URL,
    * if a username and password are present.
    * 
    * @param bool $enable Whether to turn it on or off.
    * @return URLInfo_Normalizer
    */
    public function enableAuth(bool $enable=true) : URLInfo_Normalizer
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
        return 'tel://'.$this->info->getHost();
    }
    
    protected function normalize_email() : string
    {
        return 'mailto:'.$this->info->getPath();
    }
    
    protected function normalize_url() : string
    {
        $normalized = $this->info->getScheme().'://';
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
        if(!$this->info->hasUsername() || !$this->auth) {
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
        
        return $normalized . '?'.http_build_query($params);
    }
    
    protected function renderFragment(string $normalized) : string
    {
        if(!$this->info->hasFragment()) {
            return $normalized;
        }
        
        return $normalized . '#'.$this->info->getFragment();
    }
}
