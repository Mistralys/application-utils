<?php
/**
 * File containing the {@link Request_RefreshParams} class.
 * @package Application Utils
 * @subpackage Request
 * @see Request_RefreshParams
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Collects request parameters that can be used to refresh
 * a page, maintaining all parameters needed to return to
 * the same page.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_RefreshParams implements Interface_Optionable
{
    use Traits_Optionable;
    
   /**
    * @var array<string,mixed>
    */
    private $overrides = array();
    
   /**
    * @var Request_RefreshParams_Exclude[]
    */
    private $excludes = array();
    
    public function getDefaultOptions() : array
    {
        return array(
            'exclude-session-name' => true,
            'exclude-quickform-submitter' => true
        );
    }
    
   /**
    * Whether to automatically exclude the session variable
    * from the parameters.
    * 
    * @param bool $exclude
    * @return Request_RefreshParams
    */
    public function setExcludeSessionName(bool $exclude=true) : Request_RefreshParams
    {
        $this->setOption('exclude-session-name', $exclude);
        return $this;
    }
    
   /**
    * Whether to automatically exclude the HTML_QuickForm2
    * request variable used to track whether a form has been
    * submitted.
    * 
    * @param bool $exclude
    * @return Request_RefreshParams
    */
    public function setExcludeQuickform(bool $exclude) : Request_RefreshParams
    {
        $this->setOption('exclude-quickform-submitter', $exclude);
        return $this;
    }
    
    public function excludeParamByName(string $paramName) : Request_RefreshParams
    {
        if($paramName !== '')
        {
            $this->excludes[] = new Request_RefreshParams_Exclude_Name($paramName);
        }
        
        return $this;
    }
    
   /**
    * Exclude a request using a callback function.
    * 
    * The function gets two parameters:
    * 
    * - The name of the request parameter
    * - The value of the request parameter
    * 
    * If the callback returns a boolean true, the
    * parameter will be excluded.
    * 
    * @param callable $callback
    * @return Request_RefreshParams
    */
    public function excludeParamByCallback($callback) : Request_RefreshParams
    {
        $this->excludes[] = new Request_RefreshParams_Exclude_Callback($callback);
        
        return $this;
    }
    
   /**
    * Excludes a request parameter by name.
    * 
    * @param array $paramNames
    * @return Request_RefreshParams
    */
    public function excludeParamsByName(array $paramNames) : Request_RefreshParams
    {
        foreach($paramNames as $name)
        {
            $this->excludeParamByName((string)$name);
        }
        
        return $this;
    }
    
   /**
    * Overrides a parameter: even if it exists, this
    * value will be used instead - even if it is on 
    * the list of excluded parameters.
    * 
    * @param string $paramName
    * @param mixed $paramValue
    * @return Request_RefreshParams
    */
    public function overrideParam(string $paramName, $paramValue) : Request_RefreshParams
    {
        $this->overrides[$paramName] = $paramValue;
        
        return $this;
    }
    
   /**
    * Overrides an array of parameters. 
    * 
    * @param array $params
    * @return Request_RefreshParams
    */
    public function overrideParams(array $params) : Request_RefreshParams
    {
        foreach($params as $name => $value)
        {
            $this->overrideParam((string)$name, $value);
        }
        
        return $this;
    }
    
   /**
    * Resolves all the parameter exclusions that should
    * be applied to the list of parameters. This includes
    * the manually added exclusions and the dynamic exclusions
    * like the session name.
    * 
    * @return Request_RefreshParams_Exclude[]
    */
    private function resolveExcludes() : array
    {
        $excludes = $this->excludes;
        
        $this->autoExcludeSessionName($excludes);
        $this->autoExcludeQuickform($excludes);
        
        return $excludes;
    }
    
   /**
    * Automatically excludes the session name from the
    * parameters, if present.
    * 
    * @param Request_RefreshParams_Exclude[] $excludes
    */
    private function autoExcludeSessionName(array &$excludes) : void
    {
        if($this->getBoolOption('exclude-session-name'))
        {
            $excludes[] = new Request_RefreshParams_Exclude_Name(session_name());
        }
    }
   
   /**
    * Automatically excludes the HTML_QuickForm2 submit
    * tracking variable, when enabled.
    * 
    * @param Request_RefreshParams_Exclude[] $excludes
    */
    private function autoExcludeQuickform(array &$excludes) : void
    {
        if($this->getBoolOption('exclude-quickform-submitter'))
        {
            $excludes[] = new Request_RefreshParams_Exclude_Callback(function(string $paramName)
            {
                return strstr($paramName, '_qf__') !== false;
            });
        }
    }
    
   /**
    * Retrieves the list of parameters matching the 
    * current settings.
    * 
    * @return array<string,mixed>
    */
    public function getParams() : array
    {
        $params = $this->removeExcluded($_REQUEST);
        
        // Note: using this loop instead of array_merge,
        // because array_merge has weird behavior when
        // using numeric keys.
        foreach($this->overrides as $name => $val)
        {
            $params[$name] = $val;
        }
        
        return $params;
    }
    
   /**
    * Removes all excluded parameters from the array.
    * 
    * @param array<string,mixed> $params
    * @return array<string,mixed>
    */
    private function removeExcluded(array $params) : array
    {
        $result = array();
        
        foreach($params as $name => $value)
        {
            $name = (string)$name;
            
            if(!$this->isExcluded($name, $value))
            {
                $result[$name] = $value;
            }
        }
        
        return $result;
    }
    
   /**
    * Checks all configured exclusions to see if the 
    * parameter should be excluded or not.
    * 
    * @param string $paramName
    * @param mixed $paramValue
    * @return bool
    */
    public function isExcluded(string $paramName, $paramValue) : bool
    {
        $excludes = $this->resolveExcludes();
        
        foreach($excludes as $exclude)
        {
            if($exclude->isExcluded($paramName, $paramValue))
            {
                return true;
            }
        }
        
        return false;
    }
}
