<?php
/**
 * File containing the {@see AppUtils\VariableInfo} class.
 *
 * @package Application Utils
 * @subpackage VariableInfo
 * @see AppUtils\VariableInfo
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Class used to retrieve information on variable types
 * in an object oriented way, with way to convert these 
 * to human readable formats.
 *
 * @package Application Utils
 * @subpackage VariableInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class VariableInfo implements Interface_Optionable
{
    use Traits_Optionable;
    
    const TYPE_DOUBLE = 'double';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_RESOURCE = 'resource';
    const TYPE_NULL = 'null';
    const TYPE_UNKNOWN = 'unknown type';
    const TYPE_CALLABLE = 'callable';

    const ERROR_INVALID_SERIALIZED_DATA = 56301;
    
   /**
    * @var string
    */
    protected $string;
    
   /**
    * @var mixed
    */
    protected $value;
    
   /**
    * @var string
    */
    protected $type;

    /**
     * @var string[]
     */
    protected $callableTypes = array(
        'string',
        'object',
        'array',
        'closure'
    );

    /**
     * @param mixed $value
     * @param array|null $serialized
     * @throws BaseException
     */
    public function __construct($value, ?array $serialized=null)
    {
        if(is_array($serialized))
        {
            $this->parseSerialized($serialized);
        }
        else
        {
            $this->parseValue($value);
        }
    }

    /**
     * Creates a new variable info instance from a PHP variable
     * of any type.
     *
     * @param mixed $variable
     * @return VariableInfo
     * @throws BaseException
     */
    public static function fromVariable($variable) : VariableInfo
    {
        return new VariableInfo($variable);
    }

    /**
     * Restores a variable info instance using a previously serialized
     * array using the serialize() method.
     *
     * @param array $serialized
     * @return VariableInfo
     * @throws BaseException
     * @see VariableInfo::serialize()
     */
    public static function fromSerialized(array $serialized) : VariableInfo
    {
        return new VariableInfo(null, $serialized);
    }
    
   /**
    * Parses a previously serialized data set to restore the 
    * variable information from it.
    * 
    * @param array $serialized
    * @throws BaseException
    * 
    * @see VariableInfo::ERROR_INVALID_SERIALIZED_DATA
    */
    protected function parseSerialized(array $serialized) : void
    {
        if(!isset($serialized['string']) || !isset($serialized['type']) || !isset($serialized['options']))
        {
            throw new BaseException(
                'Invalid variable info serialized data.',
                'The serialized data does not contain the expected keys.',
                self::ERROR_INVALID_SERIALIZED_DATA
            );
        }
        
        $this->string = $serialized['string'];
        $this->type = $serialized['type'];
        
        $this->setOptions($serialized['options']);
    }

    protected function parseValue($value)
    {
        $this->value = $value;
        $this->type = strtolower(gettype($value));
        
        // Gettype will return a string like "Resource(closed)" when
        // working with a resource that has already been closed.
        if(strstr($this->type, 'resource'))
        {
            $this->type = self::TYPE_RESOURCE;
        }

        if(in_array($this->type, $this->callableTypes) && is_callable($value)) {
            $this->type = self::TYPE_CALLABLE;
        }
        
        $this->string = $this->_toString();
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
   /**
    * The variable type - this is the same string that
    * is returned by the PHP function `gettype`.
    * 
    * @return string
    */
    public function getType() : string
    {
        return $this->type;
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'prepend-type' => false,
            'cut-length' => 1000
        );
    }
    
   /**
    * Whether to prepend the variable type before the value, 
    * like the var_dump function. Example: <code>string "Some text"</code>.
    * 
    * @param bool $enable
    * @return VariableInfo
    */
    public function enableType(bool $enable=true) : VariableInfo
    {
        return $this->setOption('prepend-type', $enable);
    }
    
    public function toString() : string
    {
        $converted = $this->string;
        
        if($this->getOption('prepend-type') === true && !$this->isNull())
        {
            if($this->isString())
            {
                $converted = '"'.$converted.'"';
            }
            
            $converted = $this->type.' '.$converted;
        }
        
        return $converted;
    }
    
    protected function _toString() : string
    {
        return $this->createRenderer('String')->render();
    }
    
    protected function _toHTML() : string
    {
        $type = str_replace(' ', '_', $this->type);
        $varMethod = 'toHTML_'.$type;
        return $this->$varMethod();
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
    public function isInteger() : bool
    {
        return $this->isType(self::TYPE_INTEGER);
    }
    
    public function isString() : bool
    {
        return $this->isType(self::TYPE_STRING);
    }
    
    public function isBoolean() : bool
    {
        return $this->isType(self::TYPE_BOOLEAN);
    }
    
    public function isDouble() : bool
    {
        return $this->isType(self::TYPE_DOUBLE);
    }
    
    public function isArray() : bool
    {
        return $this->isType(self::TYPE_ARRAY);
    }
    
    public function isNull() : bool
    {
        return $this->isType(self::TYPE_NULL);
    }
    
    public function isResource() : bool
    {
        return $this->isType(self::TYPE_RESOURCE);
    }
    
    public function isObject() : bool
    {
        return $this->isType(self::TYPE_OBJECT);
    }
    
    public function isType(string $type) : bool
    {
        return $this->type === $type;
    }

    public function toHTML() : string
    {
        return $this->createRenderer('HTML')->render();
    }
    
    protected function createRenderer(string $format) : VariableInfo_Renderer
    {
        $name = ucfirst(str_replace(' ', '', $this->type));
        $class = '\AppUtils\VariableInfo_Renderer_'.$format.'_'.$name;
        
        return new $class($this);
    }
    
    public function serialize()
    {
        return array(
            'type' => $this->type,
            'string' => $this->toString(), 
            'options' => $this->getOptions()
        );
    }
}
