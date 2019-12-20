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
    * @var array
    */
    protected $options;
    
   /**
    * Sets an option to the specified value. This can be any
    * kind of variable type, including objects, as needed.
    * 
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
    public function setOption(string $name, $value) : Interface_Optionable
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
    * @param array $options
    * @return Interface_Optionable
    */
    public function setOptions(array $options) : Interface_Optionable
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
    
   /**
    * Treats an option as an array, and returns its value
    * only if it contains an array - otherwise, an empty
    * array is returned.
    * 
    * @param string $name
    * @return array
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
    * @return array
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

/**
 * Interface for classes that use the optionable trait.
 * The trait itself fulfills most of the interface, but
 * it is used to guarantee internal type checks will work,
 * as well as ensure the abstract methods are implemented.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Traits_Optionable
 */
interface Interface_Optionable
{
    function setOption(string $name, $value) : Interface_Optionable;
    function getOption(string $name, $default=null);
    function setOptions(array $options) : Interface_Optionable;
    function getOptions() : array;
    function isOption(string $name, $value) : bool;
    function hasOption(string $name) : bool;
    function getDefaultOptions() : array;
}
