<?php
/**
 * File containing the {@link IniHelper} class.
 * @package AppUtils
 * @subpackage IniHelper
 * @see IniHelper_Section
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Container for a section in the INI document: stores
 * all ini lines contained within it, and offers methods
 * to handle the values.
 *
 * @package AppUtils
 * @subpackage IniHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class IniHelper_Section
{
   /**
    * @var IniHelper
    */
    protected $ini;
    
   /**
    * @var string
    */
    protected $name;
    
   /**
    * @var IniHelper_Line[]
    */
    protected $lines = array();
    
    public function __construct(IniHelper $ini, string $name)
    {
        $this->ini = $ini;
        $this->name = $name;
    }
    
   /**
    * The section's name.
    * @return string
    */
    public function getName() : string
    {
        return $this->name;
    }
    
   /**
    * Whether this is the default section: this 
    * is used internally to store all variables that
    * are not in any specific section.
    * 
    * @return bool
    */
    public function isDefault() : bool
    {
        return $this->name === IniHelper::SECTION_DEFAULT;
    }
    
   /**
    * Adds a line instance to the section.
    * 
    * @param IniHelper_Line $line
    * @return IniHelper_Section
    */
    public function addLine(IniHelper_Line $line) : IniHelper_Section
    {
        $this->lines[] = $line;
        
        return $this;
    }
    
   /**
    * Converts the values contained in the section into 
    * an associative array.
    * 
    * @return array
    */
    public function toArray() : array
    {
        $result = array();
        
        foreach($this->lines as $line)
        {
            if(!$line->isValue()) {
                continue;
            }

            $name = $line->getVarName();
            
            if(!isset($result[$name])) 
            {
                $result[$name] = $line->getVarValue();
                continue;
            }
            
            // name exists in collection? Then this is a
            // duplicate key and we need to convert it to
            // an indexed array of values.
            if(!is_array($result[$name])) 
            {
                $result[$name] = array($result[$name]);
            }
            
            $result[$name][] = $line->getVarValue();
        }
        
        return $result;
    }
    
   /**
    * Converts the section's lines into an INI string.
    * 
    * @return string
    */
    public function toString()
    {
        $lines = array();
        if(!$this->isDefault()) 
        {
            $lines[] = '['.$this->getName().']';
        }
        
        foreach($this->lines as $line) 
        {
            // we already did this
            if($line->isSection()) {
                continue;
            }
            
            $lines[] = $line->toString();
        }
        
        return implode($this->ini->getEOLChar(), $lines);
    }

   /**
    * Deletes a line from the section.
    * 
    * @param IniHelper_Line $toDelete
    * @return IniHelper_Section
    */
    public function deleteLine(IniHelper_Line $toDelete) : IniHelper_Section
    {
        $keep = array();
        
        foreach($this->lines as $line)
        {
            if($line !== $toDelete) {
                $keep[] = $line;
            }
        }
        
        $this->lines = $keep;
        
        return $this;
    }
    
   /**
    * Sets the value of a variable, overwriting any existing value.
    * 
    * @param string $name
    * @param mixed $value If an array is specified, it is treated as duplicate keys and will add a line for each value.
    * @return IniHelper_Section
    */
    public function setValue(string $name, $value) : IniHelper_Section
    {
        $lines = $this->getLinesByVariable($name);
        
        // array value? Treat it as duplicate keys.
        // Removes any superfluous values that may
        // already exist, if there are more than the
        // new set of values.
        if(is_array($value))
        {
            $values = array_values($value);
            $amountNew = count($values);
            $amountExisting = count($lines);
            
            $max = $amountNew;
            if($amountExisting > $max) {
                $max = $amountExisting;
            }
            
            for($i=0; $i < $max; $i++) 
            {
                // new value exists
                if(isset($values[$i]))
                {
                    if(isset($lines[$i])) {
                        $lines[$i]->setValue($values[$i]);
                    } else {
                        $this->addValueLine($name, $values[$i]);
                    }
                }
                else 
                {
                    $this->deleteLine($lines[$i]);
                }
            }
        }
        
        // single value: if duplicate keys exist, they
        // are removed and replaced by a single value.
        else
        {
            // remove all superfluous lines
            if(!empty($lines))
            {
                $line = array_shift($lines); // keep only the first line
                $line->setValue($value);
                
                foreach($lines as $delete) {
                    $this->deleteLine($delete);
                }
            }
            else 
            {
                $this->addValueLine($name, $value);
            }
        }
        
        return $this;
    }
    
   /**
    * Adds a variable value to the section. Unlike setValue(), this
    * will not overwrite any existing value. If the name is an existing
    * variable name, it will be converted to duplicate keys.
    * 
    * @param string $name
    * @param mixed $value If this is an array, it will be treated as duplicate keys, and all values that are not present yet will be added.
    * @return IniHelper_Section
    */
    public function addValue(string $name, $value) : IniHelper_Section
    {
        // array value? Treat it as duplicate keys.
        if(is_array($value))
        {
            $values = array_values($value);
            
            foreach($values as $setValue)
            {
                $this->addValue($name, $setValue);
            }
            
            return $this;
        }
        
        $lines = $this->getLinesByVariable($name);
        
        if(empty($lines))
        {
            $this->addValueLine($name, $value);
        }
        else
        {
            $found = false;
            
            foreach($lines as $line)
            {
                if($line->getVarValue() === $value) {
                    $found = $line;
                    break;
                }
            }
            
            if(!$found)
            {
                $this->addValueLine($name, $value);
            }
        }
        
        return $this;
    }
    
    protected function addValueLine(string $name, $value) : IniHelper_Line
    {
        $line = new IniHelper_Line(
            sprintf('%s=%s', $name, 'dummyvalue'),
            0
        );
        
        $line->setValue($value);
        
        $this->addLine($line);
        
        return $line;
    }
    
    
   /**
    * Retrieves all lines for the specified variable name.
    *  
    * @param string $name
    * @return \AppUtils\IniHelper_Line[]
    */
    public function getLinesByVariable(string $name)
    {
        $result = array();
        
        foreach($this->lines as $line)
        {
            if($line->isValue() && $line->getVarName() === $name) {
                $result[] = $line;
            }
        }
        
        return $result;
    }
}
