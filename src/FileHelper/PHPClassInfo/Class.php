<?php
/**
 * File containing the {@link FileHelper_PHPClassInfo_Class} class.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @see FileHelper_PHPClassInfo_Class
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Container for the information on a single class found
 * in a PHP file. Used to easily access all available 
 * class details.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileHelper_PHPClassInfo_Class 
{
   /**
    * @var FileHelper_PHPClassInfo
    */
    protected $info;

   /**
    * @var bool
    */
    protected $abstract = false;
    
   /**
    * @var bool
    */
    protected $final = false;
    
   /**
    * @var string
    */
    protected $extends = '';
    
   /**
    * @var string[]
    */
    protected $implements = array();
    
   /**
    * @var string
    */
    protected $name;
    
   /**
    * @var string
    */
    protected $declaration;
    
   /**
    * @var string
    */
    protected $keyword;

    /**
     * @var string
     */
    private $type;

    /**
    * @param FileHelper_PHPClassInfo $info The class info instance.
    * @param string $declaration The full class declaration, e.g. "class SomeName extends SomeOtherClass".
    * @param string $keyword The class keyword, if any, i.e. "abstract" or "final".
    */
    public function __construct(FileHelper_PHPClassInfo $info, string $declaration, string $keyword, string $type)
    {
        $this->info = $info;
        $this->declaration = $declaration;
        $this->keyword = $keyword;
        $this->type = $type;
        
        $this->analyzeCode();
    }
    
   /**
    * Check if this class is a subclass of the specified
    * class name.
    * 
    * @param string $className
    * @return bool
    */
    public function isSublassOf(string $className) : bool
    {
        return is_subclass_of($this->getNameNS(), $className);
    }
    
   /**
    * The class name without namespace.
    * @return string
    */
    public function getName() : string
    {
        return $this->name;
    }
    
   /**
    * The absolute class name with namespace (if any).
    * @return string
    */
    public function getNameNS() : string
    {
        $name = $this->getName();
        
        if($this->info->hasNamespace()) {
            $name = $this->info->getNamespace().'\\'.$this->name;
        }
        
        return $name;
    }
    
   /**
    * Whether it is an abstract class.
    * @return bool
    */
    public function isAbstract() : bool
    {
        return $this->abstract;
    }
    
   /**
    * Whether it is a final class.
    * @return bool
    */
    public function isFinal() : bool
    {
        return $this->final;
    }

   /**
    * The name of the class that this class extends (with namespace, if specified).
    * @return string
    */
    public function getExtends() : string
    {
        return $this->extends;
    }
    
   /**
    * A list of interfaces the class implements, if any.
    * @return string[]
    */
    public function getImplements() : array
    {
        return $this->implements;
    }
    
   /**
    * The class declaration string, with normalized spaces and sorted interface names.
    * NOTE: does not include the keyword "abstract" or "final".
    * 
    * @return string
    */
    public function getDeclaration() : string
    {
        $parts = array();
        $parts[] = $this->type;
        $parts[] = $this->getName();
        
        if(!empty($this->extends)) {
            $parts[] = 'extends';
            $parts[] = $this->extends;
        }
        
        if(!empty($this->implements)) {
            $parts[] = 'implements';
            $parts[] = implode(', ', $this->implements);
        }
        
        return implode(' ', $parts);
    }
    
   /**
    * The keyword before "class", e.g. "abstract".
    * @return string
    */
    public function getKeyword() : string
    {
        return $this->keyword;
    }

    public function isTrait() : bool
    {
        return $this->type === 'trait';
    }

    protected function analyzeCode() : void
    {
        if($this->keyword === 'abstract') {
            $this->abstract = true;
        } else if($this->keyword === 'final') {
            $this->final = true;
        }
        
        $declaration = $this->declaration;
        
        $parts = ConvertHelper::explodeTrim(' ', $declaration);
        
        $this->name = trim(array_shift($parts));
        
        $tokens = array(
            'implements' => array(),
            'extends' => array()
        );
        
        $tokenName = 'none';
        
        foreach($parts as $part)
        {
            $part = str_replace(',', '', $part);
            $part = trim($part);
            if(empty($part)) {
                continue;
            }
            
            $name = strtolower($part);
            if($name === 'extends' || $name === 'implements') {
                $tokenName = $name;
                continue;
            }
            
            $tokens[$tokenName][] = $part;
        }
        
        $this->implements = $tokens['implements'];
        
        if(!empty($this->implements)) {
            usort($this->implements, function(string $a, string $b) {
                return strnatcasecmp($a, $b);
            });
        }
        
        if(!empty($tokens['extends'])) {
            $this->extends = $tokens['extends'][0];
        }
    }
}
