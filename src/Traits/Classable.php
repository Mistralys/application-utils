<?php
/**
 * File containing the {@see AppUtils\Traits_Classable} trait,
 * and the matching interface.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see Traits_Classable
 * @see Interface_Classable
 */

namespace AppUtils;

/**
 * Trait for handling HTML classes.
 *
 * NOTE: To add this to a class, it must use the trait,
 * but also implement the interface.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Interface_Classable
 */
trait Traits_Classable
{
    protected $classes = array();
    
    public function addClass(string $name)
    {
        if(!in_array($name, $this->classes)) {
            $this->classes[] = $name;
        }
        
        return $this;
    }
    
    public function addClasses(array $names)
    {
        foreach($names as $name) {
            $this->addClass($name);
        }
        
        return $this;
    }
    
    public function hasClass(string $name) : bool
    {
        return in_array($name, $this->classes);
    }
    
    public function removeClass(string $name)
    {
        $idx = array_search($name, $this->classes);
        
        if($idx !== false) {
            unset($this->classes[$idx]);
            sort($this->classes);
        }
        
        return $this;
    }
    
    public function getClasses() : array
    {
        return $this->classes;
    }
    
    public function classesToString() : string
    {
        return implode(' ', $this->classes);
    }
}

/**
 * Interface for classes that use the classable trait.
 * The trait itself fulfills most of the interface, but
 * it is used to guarantee internal type checks will work,
 * as well as ensure the abstract methods are implemented.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Traits_Classable
 */
interface Interface_Classable
{
   /**
    * @param string $name
    * @return $this
    */
    public function addClass(string $name);

   /**
    * @param array $names
    * @return $this
    */
    public function addClasses(array $names);
    
   /**
    * @param string $name
    * @return bool
    */
    public function hasClass(string $name) : bool;
    
   /**
    * @param string $name
    * @return $this
    */
    public function removeClass(string $name);
    
   /**
    * @return array
    */
    public function getClasses() : array;
    
   /**
    * @return string
    */
    public function classesToString() : string;
}
