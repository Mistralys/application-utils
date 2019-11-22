<?php

declare(strict_types=1);

namespace AppUtils;

class IniHelper_Section
{
   /**
    * @var string
    */
    protected $name;
    
   /**
    * @var IniHelper_Line[]
    */
    protected $lines = array();
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public function getName() : string
    {
        return $this->name;
    }
    
    public function isDefault() : bool
    {
        return $this->name === IniHelper::SECTION_DEFAULT;
    }
    
    public function addLine(IniHelper_Line $line) : IniHelper_Section
    {
        $this->lines[] = $line;
        
        return $this;
    }
    
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
        
        return implode("\n", $lines);
    }
    
    public function createValueLine(string $name, $value) : IniHelper_Line
    {
        return new IniHelper_Line(
            sprintf('%s=%s', $name, $value), 
            0
        );
    }
    
    public function setValue(string $name, $value) : IniHelper_Section
    {
        $line = $this->getLineByVariable($name);
        if($line !== null) {
            $line->setValue($value);
        } else {
            $this->addLine($this->createValueLine($name, $value));
        }
        
        return $this;
    }
    
    public function getLineByVariable(string $name) : ?IniHelper_Line
    {
        foreach($this->lines as $line)
        {
            if($line->getVarName() === $name) {
                return $line;
            }
        }
        
        return null;
    }
}
