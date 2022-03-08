<?php
/**
 * File containing the {@see AppUtils\Traits_Optionable} trait,
 * and the matching interface.
 * 
 * @package Application Utils
 * @subpackage Traits
 * @see Traits_Optionable
 * @see Interface_Optionable
 */

namespace AppUtils;

/**
 * Trait for adding options to a class: allows setting
 * and getting options of all types.
 * 
 * NOTE: To add this to a class, it must use the trait, 
 * but also implement the interface.
 * 
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see Interface_Optionable
 */
trait Traits_Optionable
{
   /**
    * @var array<string,mixed>|NULL
    */
    protected ?array $options = null;

    /**
     * Sets an option to the specified value. This can be any
     * kind of variable type, including objects, as needed.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setOption(string $name, $value) : self
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        $this->options[$name] = $value;
        return $this;
    }
    
   /**
    * Sets a collection of options at once, from an
    * associative array.
    * 
    * @param array<string,mixed> $options
    * @return $this
    */
    public function setOptions(array $options) : self
    {
        foreach($options as $name => $value) {
            $this->setOption($name, $value);
        }
        
        return $this;
    }
    
   /**
    * Retrieves an option's value.
    * 
    * NOTE: Use the specialized type getters to ensure an option
    * contains the expected type (for ex. getArrayOption()). 
    * 
    * @param string $name
    * @param mixed $default The default value to return if the option does not exist.
    * @return mixed
    */
    public function getOption(string $name, $default=null)
    {
        if(!isset($this->options))
        {
            $this->options = $this->getDefaultOptions();
        }

        return $this->options[$name] ?? $default;
    }
    
   /**
    * Enforces that the option value is a string. Numbers are converted
    * to string, strings are passed through, and all other types will 
    * return the default value. The default value is also returned if
    * the string is empty.
    * 
    * @param string $name
    * @param string $default Used if the option does not exist, is invalid, or empty.
    * @return string
    */
    public function getStringOption(string $name, string $default='') : string
    {
        $value = $this->getOption($name, false);
        
        if((is_string($value) || is_numeric($value)) && !empty($value)) {
            return (string)$value;
        }
        
        return $default;
    }

    /**
     * Treats the option value as a boolean value: will return
     * true if the value actually is a boolean true.
     *
     * NOTE: boolean string representations are not accepted.
     *
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public function getBoolOption(string $name, bool $default=false) : bool
    {
        if($this->getOption($name) === true)
        {
            return true;
        }
        
        return $default;
    }
    
   /**
    * Treats the option value as an integer value: will return
    * valid integer values (also from integer strings), or the
    * default value otherwise.
    * 
    * @param string $name
    * @param int $default
    * @return int
    */
    public function getIntOption(string $name, int $default=0) : int
    {
        $value = $this->getOption($name);
        if(ConvertHelper::isInteger($value)) {
            return (int)$value;
        }
        
        return $default;
    }
    
   /**
    * Treats an option as an array, and returns its value
    * only if it contains an array - otherwise, an empty
    * array is returned.
    * 
    * @param string $name
    * @return array<int|string,mixed>
    */
    public function getArrayOption(string $name) : array
    {
        $val = $this->getOption($name);
        if(is_array($val)) {
            return $val;
        }
        
        return array();
    }
    
   /**
    * Checks whether the specified option exists - even
    * if it has a NULL value.
    * 
    * @param string $name
    * @return bool
    */
    public function hasOption(string $name) : bool
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return array_key_exists($name, $this->options);
    }
    
   /**
    * Returns all options in one associative array.
    *
    * @return array<string,mixed>
    */
    public function getOptions() : array
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return $this->options;
    }
    
   /**
    * Checks whether the option's value is the one specified.
    * 
    * @param string $name
    * @param mixed $value
    * @return bool
    */
    public function isOption(string $name, $value) : bool
    {
        return $this->getOption($name) === $value;
    }
}
