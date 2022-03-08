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
   /**
    * @var string[]
    */
    protected array $classes = array();

    public function hasClasses() : bool
    {
        return !empty($this->classes);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function addClass(string $name) : self
    {
        if(!in_array($name, $this->classes, true)) {
            $this->classes[] = $name;
        }
        
        return $this;
    }

    /**
     * @param string[] $names
     * @return $this
     */
    public function addClasses(array $names) : self
    {
        foreach($names as $name) {
            $this->addClass($name);
        }
        
        return $this;
    }
    
    public function hasClass(string $name) : bool
    {
        return in_array($name, $this->classes, true);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeClass(string $name) : self
    {
        $idx = array_search($name, $this->classes, true);
        
        if($idx !== false) {
            unset($this->classes[$idx]);
            sort($this->classes);
        }
        
        return $this;
    }
    
   /**
    * Retrieves a list of all classes, if any.
    * 
    * @return string[]
    */
    public function getClasses() : array
    {
        return $this->classes;
    }
    
   /**
    * Renders the class names list as space-separated string for use in an HTML tag.
    * 
    * @return string
    */
    public function classesToString() : string
    {
        return implode(' ', $this->classes);
    }
    
   /**
    * Renders the "class" attribute string for inserting into an HTML tag.
    * @return string
    */
    public function classesToAttribute() : string
    {
        if(!empty($this->classes))
        {
            return sprintf(
                ' class="%s" ',
                $this->classesToString()
            );
        }
        
        return '';
    }
}
