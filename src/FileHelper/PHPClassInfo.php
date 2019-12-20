<?php
/**
 * File containing the {@link FileHelper_PHPClassInfo} class.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @see FileHelper_PHPClassInfo
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Used to retrieve information on the PHP classes contained
 * within the target PHP file. Does not use the reflection API.
 * This is meant as a quick way to check for the presence of
 * classes, and which classes they extend or interfaces are 
 * implemented.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see FileHelper::findPHPClasses()
 */
class FileHelper_PHPClassInfo
{
    const ERROR_TARGET_FILE_DOES_NOT_EXIST = 41401;
    
    const ERROR_CANNOT_READ_FILE = 41402;
    
    /**
     * @var string
     */
    protected $path;
    
    protected $classes = array();
    
   /**
    * The namespace detected in the file, if any.
    * @var string
    */
    protected $namespace = '';
    
   /**
    * @param string $path The path to the PHP file to parse.
    * @throws FileHelper_Exception
    * @see FileHelper::findPHPClasses()
    */
    public function __construct(string $path)
    {
        $this->path = realpath($path);
        
        if($this->path === false)
        {
            throw new FileHelper_Exception(
                'Cannot extract file information: file not found.',
                sprintf(
                    'Tried analyzing file at [%s].',
                    $path
                ),
                self::ERROR_TARGET_FILE_DOES_NOT_EXIST
            );
        }
        
        $this->parseFile();
    }
    
   /**
    * The name of the namespace of the classes in the file, if any.
    * @return string
    */
    public function getNamespace() : string
    {
        return $this->namespace;
    }
    
   /**
    * Whether the file contains a namespace.
    * @return bool
    */
    public function hasNamespace() : bool
    {
        return !empty($this->namespace);
    }
    
   /**
    * The absolute path to the file.
    * @return string
    */
    public function getPath() : string
    {
        return $this->path;
    }
   
   /**
    * Whether any classes were found in the file.
    * @return bool
    */
    public function hasClasses() : bool
    {
        return !empty($this->classes);
    }
    
   /**
    * The names of the classes that were found in the file (with namespace if any).
    * @return string[]
    */
    public function getClassNames() : array
    {
        return array_keys($this->classes);
    }
    
   /**
    * Retrieves all classes that were detected in the file,
    * which can be used to retrieve more information about
    * them.
    * 
    * @return FileHelper_PHPClassInfo_Class[]
    */
    public function getClasses()
    {
        return $this->classes;
    }
    
   /**
    * @throws FileHelper_Exception
    */
    protected function parseFile()
    {
        $code = file_get_contents($this->path);
        
        if($code === false) 
        {
            throw new FileHelper_Exception(
                'Cannot open file for parsing.',
                sprintf(
                    'The file to parse exists, but it cannot be opened for reading, located at [%s].',
                    $this->path
                ),
                self::ERROR_CANNOT_READ_FILE
            );
        }
        
        // require the file so all interfaces etc. are loaded:
        // this is need for the is_subclass_of function to work
        // properly, as it does not do this.
        require_once $this->path;
        
        $result = array();
        preg_match_all('/namespace[\s]+([^;]+);/six', $code, $result, PREG_PATTERN_ORDER);
        if(isset($result[0]) && isset($result[0][0])) {
            $this->namespace = trim($result[1][0]);
        }
        
        $result = array();
        preg_match_all('/(abstract|final)[\s]+class[\s]+([\sa-z0-9\\\\_,]+){|class[\s]+([\sa-z0-9\\\\_,]+){/six', $code, $result, PREG_PATTERN_ORDER);

        if(!isset($result[0]) || !isset($result[0][0])) {
            return;
        }
        
        $indexes = array_keys($result[0]);
        
        foreach($indexes as $idx)
        {
            $keyword = $result[1][$idx];
            $declaration = $result[2][$idx];
            if(empty($keyword)) {
                $declaration = $result[3][$idx];
            }
            
            $class = new FileHelper_PHPClassInfo_Class(
                $this, 
                $this->stripWhitespace($declaration), 
                trim($keyword)
            );
            
            $this->classes[$class->getNameNS()] = $class;
        }
    }
    
   /**
    * Strips all whitespace from the string, replacing it with 
    * regular spaces (newlines, tabs, etc.).
    * 
    * @param string $string
    * @return string
    */
    protected function stripWhitespace(string $string) : string 
    {
        return preg_replace('/[\s]/', ' ', $string);
    }
}
