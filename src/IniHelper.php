<?php

declare(strict_types=1);

namespace AppUtils;

class IniHelper
{
    const SECTION_DEFAULT = '__inihelper_section_default';
    
    const ERROR_TARGET_FILE_NOT_FOUND = 41801;
    
    const ERROR_TARGET_FILE_NOT_READABLE = 41802;
    
    protected $sections = array();
    
    function __construct(string $iniString)
    {
        $lines = explode("\n", $iniString);
        
        $total = count($lines);
        
        $section = $this->addSection(self::SECTION_DEFAULT);
        
        for($i=0; $i < $total; $i++) 
        {
            $line = new IniHelper_Line($lines[$i], $i);
            
            if($line->isSection()) {
                $section = $this->addSection($line->getSectionName());
            }

            $section->addLine($line);
        }
    }
    
    public static function fromFile(string $iniPath)
    {
        $path = realpath($iniPath);
        if($path === false) 
        {
            throw new IniHelper_Exception(
                'Source ini file not found',
                sprintf(
                    'Tried to find the file at [%s].',
                    $iniPath
                ),
                self::ERROR_TARGET_FILE_NOT_FOUND
            );
        }
        
        $content = file_get_contents($iniPath);
        if($content !== false) {
            return self::fromString($content);
        }
        
        throw new IniHelper_Exception(
            'Cannot open source ini file for reading',
            sprintf(
                'Tried to open the file at [%s].',
                $iniPath
            ),
            self::ERROR_TARGET_FILE_NOT_READABLE
        );
    }
    
   /**
    * Creates a new ini helper instance from an ini string.
    * 
    * @param string $iniContent
    * @return \AppUtils\IniHelper
    */
    public static function fromString(string $iniContent)
    {
        return new IniHelper($iniContent);
    }
    
   /**
    * Adds a new data section, and returns the section instance.
    * If a section with the name already exists, returns that
    * section instead of creating a new one.
    *  
    * @param string $name
    * @return IniHelper_Section
    */
    public function addSection(string $name) : IniHelper_Section
    {
        if(!isset($this->sections[$name])) {
            $this->sections[$name] = new IniHelper_Section($name);
        }
        
        return $this->sections[$name];
    }
    
   /**
    * Gets the data from the INI file as an associative array.
    * 
    * @return array
    */
    public function toArray() : array
    {
        $result = array();
        
        foreach($this->sections as $section)
        {
            if($section->isDefault()) 
            {
                $result = array_merge($result, $section->toArray());
            } 
            else 
            {
                $result[$section->getName()] = $section->toArray();
            }
        }
        
        return $result;
    }
    
   /**
    * Saves the INI content to the target file.
    * 
    * @param string $filePath
    * @return IniHelper
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_SAVE_FOLDER_NOT_WRITABLE
    * @see FileHelper::ERROR_SAVE_FILE_NOT_WRITABLE
    * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
    */
    public function saveToFile(string $filePath) : IniHelper
    {
        FileHelper::saveFile($filePath, self::saveToString());
        
        return $this;
    }
    
   /**
    * Returns the INI content as string.
    * 
    * @return string
    */
    public function saveToString() : string
    {
        $parts = array();
        
        foreach($this->sections as $section)
        {
            $parts[] = $section->toString();
        }
        
        return implode("\n", $parts);
    }
    
    public function setValue(string $path, $value) : IniHelper
    {
        $path = explode('.', $path);
        $name = '';
        
        if(count($path) === 1) 
        {
            $section = $this->getDefaultSection();
            $name = array_pop($path);
        }
        else 
        {
            $sectionName = array_shift($path);
            $name = array_pop($path);
            
            $section = $this->addSection($sectionName);
        }
        
        $section->setValue($name, $value);
        
        return $this;
    }
    
    public function getDefaultSection() : IniHelper_Section
    {
        return $this->addSection(self::SECTION_DEFAULT);
    }
}
