<?php
/**
 * File containing the {@see AppUtils\URLInfo_Highlighter} class.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @see AppUtils\URLInfo_Highlighter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Handles highlighting a previously parsed URL. 
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLInfo_Highlighter
{
   /**
    * @var URLInfo
    */
    protected $info;
    
    public function __construct(URLInfo $info)
    {
        $this->info = $info;
    }
    
    public function highlight() : string
    {
        $method = 'highlight_'.$this->info->getType();
        
        return 
        '<span class="link">'.
            $this->$method().
        '</span>';
    }
    
    protected function highlight_email() : string
    {
        return sprintf(
            '<span class="link-scheme scheme-mailto">mailto:</span>'.
            '<span class="link-host">%s</span>',
            $this->info->getPath()
        );
    }
    
    protected function highlight_fragment() : string
    {
        return sprintf(
            '<span class="link-fragment-sign">#</span>'.
            '<span class="link-fragment-value">%s</span>',
            $this->info->getFragment()
        );
    }
    
    protected function highlight_phone() : string
    {
        return $this->highlight_url();
    }
    
    protected function highlight_url() : string
    {
        $result = [];
        
        $parts = array(
            'scheme',
            'username',
            'host',
            'port',
            'path',
            'params',
            'fragment'
        );
        
        foreach($parts as $part) 
        {
            $method = 'render_'.$part;
            $result[] = (string)$this->$method();
        }
        
        return implode('', $result);
    }
    
    protected function render_scheme() : string
    {
        if(!$this->info->hasScheme()) {
            return '';
        }
        
        return sprintf(
            '<span class="link-scheme scheme-%1$s">'.
                '%1$s:'.
            '</span>'.
            '<span class="link-component double-slashes">//</span>',
            $this->info->getScheme()
        );
    }
    
    protected function render_username() : string
    {
        if(!$this->info->hasUsername()) {
            return '';
        }
        
        return sprintf(
            '<span class="link-credentials">%s</span>'.
            '<span class="link-component credentials-separator">:</span>'.
            '<span class="link-credentials">%s</span>'.
            '<span class="link-component credentials-at">@</span>',
            $this->info->getUsername(),
            $this->info->getPassword()
        );
    }
    
    protected function render_host() : string
    {
        if(!$this->info->hasHost()) {
            return '';
        }
        
        return sprintf(
            '<span class="link-host">%s</span><wbr>',
            $this->info->getHost()
        );
    }
    
    protected function render_port() : string
    {
        if(!$this->info->hasPort()) {
            return '';
        }
        
        return sprintf(
            '<span class="link-component port-separator">:</span>'.
            '<span class="link-port">%s</span>',
            $this->info->getPort()
        );
    }
       
    protected function render_path() : string
    {
        if(!$this->info->hasPath()) {
            return '';
        }
        
        $path = str_replace(array(';', '='), array(';<wbr>', '=<wbr>'), $this->info->getPath());
        $tokens = explode('/', $path);
        $path = implode('<span class="link-component path-separator">/</span><wbr>', $tokens);
        
        return sprintf(
            '<span class="link-path">%s</span><wbr>',
            $path
        );
    }
    
    protected function render_params() : string
    {
        $params = $this->info->getParams();
        
        if(empty($params)) {
            return '';
        }
        
        $tokens = array();
        $excluded = array();
        
        if($this->info->isParamExclusionEnabled())
        {
            $excluded = $this->info->getExcludedParams();
        }
        
        foreach($params as $param => $value)
        {
            $parts = sprintf(
                '<span class="link-param-name">%s</span>'.
                '<span class="link-component param-equals">=</span>'.
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
            if(isset($excluded[$param]))            
            {
                // display the excluded parameter, but highlight it
                if($this->info->isHighlightExcludeEnabled())
                {
                    $tooltip = $excluded[$param];
                    
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
        
        return
        '<span class="link-component query-sign">?</span>'.
        implode('<span class="link-component param-separator">&amp;</span>', $tokens);
    }
     
    protected function render_fragment() : string
    {
        if(!$this->info->hasFragment()) {
            return '';
        }
        
        return sprintf(
            '<span class="link-fragment-sign">#</span>'.
            '<span class="link-fragment">%s</span>',
            $this->info->getFragment()
        );
    }
}
