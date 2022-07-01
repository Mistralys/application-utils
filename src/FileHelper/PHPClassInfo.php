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

use AppUtils\FileHelper\PHPFile;

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
    public const TYPE_CLASS = 'class';
    public const TYPE_TRAIT = 'trait';
    public const TYPE_INTERFACE = 'interface';

    protected PHPFile $file;
    protected string $namespace = '';

    /**
     * @var array<string,FileHelper_PHPClassInfo_Class>
     */
    protected array $classes = array();

    /**
     * @var string[]
     */
    private static array $types = array(
        self::TYPE_CLASS,
        self::TYPE_INTERFACE,
        self::TYPE_TRAIT
    );

   /**
    * @param PHPFile $path The path to the PHP file to parse.
    * @throws FileHelper_Exception
    * @see FileHelper::findPHPClasses()
    */
    public function __construct(PHPFile $path)
    {
        $this->file = $path
            ->requireExists()
            ->requireReadable();
        
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
        return $this->file->getPath();
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
    public function getClasses() : array
    {
        return array_values($this->classes);
    }

    protected function parseFile() : void
    {
        $code = php_strip_whitespace($this->getPath());

        $this->detectNamespace($code);
        $result = $this->detectMatches($code);

        if($result === null) {
            return;
        }
        
        $indexes = array_keys($result[0]);
        
        foreach($indexes as $idx)
        {
            $this->parseResult(
                $result[1][$idx],
                $result[3][$idx],
                $result[2][$idx],
                $result[4][$idx],
                $result[5][$idx]
            );
        }
    }

    private function detectMatches(string $code) : ?array
    {
        $types = implode('|', self::$types);
        $result = array();

        preg_match_all('/(abstract|final)\s+('.$types.')\s+([\sa-z\d\\\\_,]+){|('.$types.')\s+([\sa-z\d\\\\_,]+){/ix', $code, $result, PREG_PATTERN_ORDER);

        if(isset($result[0][0])) {
            return $result;
        }

        return null;
    }

    private function detectNamespace(string $code) : void
    {
        $result = array();
        preg_match_all('/namespace\s+([^;]+);/ix', $code, $result, PREG_PATTERN_ORDER);

        if(isset($result[0][0])) {
            $this->namespace = trim($result[1][0]);
        }
    }

    private function parseResult(string $keyword, string $declaration, string $type, string $simpleType, string $simpleDeclaration) : void
    {
        if(empty($keyword)) {
            $type = $simpleType;
            $declaration = $simpleDeclaration;
        }

        $class = new FileHelper_PHPClassInfo_Class(
            $this,
            $this->stripWhitespace($declaration),
            trim($keyword),
            $type
        );

        $this->classes[$class->getNameNS()] = $class;
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
        return preg_replace('/\s/', ' ', $string);
    }
}
