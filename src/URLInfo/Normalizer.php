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
    
    public function __construct(URLInfo $info)
    {
        $this->info = $info;
    }
    
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
        $normalized = $this->info->getScheme().'://'.$this->info->getHost();
        
        if($this->info->hasPath()) {
            $normalized .= $this->info->getPath();
        }
        
        $params = $this->info->getParams();
        if(!empty($params)) {
            $normalized .= '?'.http_build_query($params);
        }
        
        if($this->info->hasFragment()) {
            $normalized .= '#'.$this->info->getFragment();
        }
        
        return $normalized;
    }
}
