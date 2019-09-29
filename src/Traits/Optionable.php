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