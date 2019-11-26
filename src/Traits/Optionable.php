<?php

namespace AppUtils;

trait Traits_Optionable
{
    protected $options;
    
    public function setOption(string $name, $value) : Interface_Optionable
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        $this->options[$name] = $value;
        return $this;
    }
    
    public function setOptions(array $options) : Interface_Optionable
    {
        foreach($options as $name => $value) {
            $this->setOption($name, $value);
        }
        
        return $this;
    }
    
    public function getOption(string $name, $default=null)
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        if(isset($this->options[$name])) {
            return $this->options[$name];
        }
        
        return $default;
    }
    
   /**
    * Enforces that the option value is a string. Scalar 
    * values are converted to string, and non-scalar values
    * are converted to an empty string.
    * 
    * @param string $name
    * @return string
    */
    public function getStringOption(string $name) : string
    {
        $value = $this->getOption($name, false);
        
        if(is_scalar($value)) {
            return (string)$value;
        }
        
        return '';
    }
    
   /**
    * Treats the option value as a boolean value: will return
    * true if the value actually is a boolean true.
    * 
    * NOTE: boolean string representations are not accepted.
    * 
    * @param string $name
    * @return bool
    */
    public function getBoolOption(string $name) : bool
    {
        if($this->getOption($name) === true) {
            return true;
        }
        
        return false;
    }
    
    public function hasOption($name) : bool
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return array_key_exists($name, $this->options);
    }
    
    public function getOptions() : array
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return $this->options;
    }
    
    public function isOption($name, $value) : bool
    {
        return $this->getOption($name) === $value;
    }
}

interface Interface_Optionable
{
    function setOption(string $name, $value) : Interface_Optionable;
    function getOption(string $name, $default=null);
    function setOptions(array $options) : Interface_Optionable;
    function getOptions() : array;
    function isOption($name, $value) : bool;
    function hasOption($name) : bool;
    function getDefaultOptions() : array;
}