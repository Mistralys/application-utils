<?php
/**
 * File containing the {@link IniHelper} class.
 * @package Application Utils
 * @subpackage IniHelper
 * @see IniHelper
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * INI file reader and editor. Supports duplicate keys like
 * in the php.ini (list of extensions), and preserves the
 * formatting of the original file (including comments).
 * 
 * @package Application Utils
 * @subpackage IniHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class IniHelper
{
    const SECTION_DEFAULT = '__inihelper_section_default';
    
    const ERROR_TARGET_FILE_NOT_READABLE = 41802;
    
    protected $sections = array();
    
    protected $eol = "\n";
    
    protected $pathSeparator = '/';
    
    protected function __construct(string $iniString)
    {
        $section = $this->addSection(self::SECTION_DEFAULT);
        
        if(empty($iniString)) {
            return;
        }
        
        $eol = ConvertHelper::detectEOLCharacter($iniString);
        if($eol !== null) {
            $this->eol = $eol->getCharacter();
        }
        
        $lines = explode($this->eol, $iniString);
        
        $total = count($lines);
        
        for($i=0; $i < $total; $i++) 
        {
            $line = new IniHelper_Line($lines[$i], $i);
            
            if($line->isSection()) {
                $section = $this->addSection($line->getSectionName());
            }

            $section->addLine($line);
        }
    }
    
   /**
    * The end of line character used in the INI source string.
    * @return string
    */
    public function getEOLChar() : string
    {
        return $this->eol;
    }
    
   /**
    * Factory method: creates a new helper instance loading the
    * ini content from the specified file.
    * 
    * @param string $iniPath
    * @throws IniHelper_Exception
    * @return \AppUtils\IniHelper
    */
    public static function createFromFile(string $iniPath)
    {
        $iniPath = FileHelper::requireFileExists($iniPath);
        
        $content = file_get_contents($iniPath);
        if($content !== false) {
            return self::createFromString($content);
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
    * Factory method: Creates a new ini helper instance from an ini string.
    * 
    * @param string $iniContent
    * @return \AppUtils\IniHelper
    */
    public static function createFromString(string $iniContent)
    {
        return new IniHelper($iniContent);
    }
    
   /**
    * Factory method: Creates a new empty ini helper.
    *  
    * @return \AppUtils\IniHelper
    */
    public static function createNew()
    {
        return self::createFromString('');
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
            $this->sections[$name] = new IniHelper_Section($this, $name);
        }
        
        return $this->sections[$name];
    }
    
   /**
    * Retrieves a section by its name, if it exists.
    * 
    * @param string $name
    * @return IniHelper_Section|NULL
    */
    public function getSection(string $name) : ?IniHelper_Section
    {
        if(isset($this->sections[$name])) {
            return $this->sections[$name];
        }
        
        return null;
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
        FileHelper::saveFile($filePath, $this->saveToString());
        
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
        
        return implode($this->eol, $parts);
    }
    
   /**
    * Sets or adds the value of a setting in the INI content.
    * If the setting does not exist, it is added. Otherwise,
    * the existing value is overwritten.
    * 
    * @param string $path A variable path, either <code>varname</code> or <code>section.varname</code>.
    * @param mixed $value
    * @return IniHelper
    */
    public function setValue(string $path, $value) : IniHelper
    {
        $path = $this->parsePath($path);
       
        $this->addSection($path['section'])->setValue($path['name'], $value);
    
        return $this;
    }
    
    public function addValue(string $path, $value) : IniHelper
    {
        $path = $this->parsePath($path);
        
        $this->addSection($path['section'])->addValue($path['name'], $value);
        
        return $this;
    }
    
   /**
    * Checks whether a section with the specified name exists.
    * 
    * @param string $name
    * @return bool
    */
    public function sectionExists(string $name) : bool
    {
        foreach($this->sections as $section) {
            if($section->getName() === $name) {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * Retrieves the default section, which is used to add
    * values in the root of the document.
    * 
    * @return IniHelper_Section
    */
    public function getDefaultSection() : IniHelper_Section
    {
        return $this->addSection(self::SECTION_DEFAULT);
    }
    
   /**
    * Retrieves all variable lines for the specified path.
    * 
    * @param string $path A variable path. Either <code>varname</code> or <code>section.varname</code>.
    * @return array|\AppUtils\IniHelper_Line[]
    */
    public function getLinesByVariable(string $path)
    {
        $path = $this->parsePath($path);
        
        if(!$this->sectionExists($path['section'])) {
            return array();
        }
        
        return $this->addSection($path['section'])->getLinesByVariable($path['name']);
    }
    
    protected function parsePath(string $path) : array
    {
        $path = explode($this->pathSeparator, $path);
        
        if(count($path) === 1)
        {
            return array(
                'section' => self::SECTION_DEFAULT,
                'name' => trim(array_pop($path))
            );
        }

        return array(
            'section' => trim(array_shift($path)),
            'name' => trim(array_pop($path))
        );
    }
}
