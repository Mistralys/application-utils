<?php

declare(strict_types=1);

namespace AppUtils;

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

    protected static $colors = array(
        self::TYPE_DOUBLE => 'ce0237',
        self::TYPE_INTEGER => 'ce0237',
        self::TYPE_ARRAY => '027ace',
        self::TYPE_OBJECT => 'cf5e20',
        self::TYPE_RESOURCE => '1c2eb1',
        self::TYPE_STRING => '1fa507',
        self::TYPE_BOOLEAN => '1c2eb1',
        self::TYPE_NULL => '1c2eb1',
        self::TYPE_UNKNOWN => 'cc0000',
        self::TYPE_CALLABLE => 'cf5e20'
    );
    
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
    * @param mixed $value
    * @param array|null $serialized
    */
    public function __construct($value, $serialized=null)
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
    * @see VariableInfo::serialize()
    */
    public static function fromSerialized(array $serialized) : VariableInfo
    {
        return new VariableInfo(null, $serialized);
    }
    
    protected function parseSerialized(array $serialized)
    {
        $this->string = $serialized['string'];
        $this->type = $serialized['type'];
        
        $this->setOptions($serialized['options']);
    }
    
    protected function parseValue($value)
    {
        $this->value = $value;
        $this->type = strtolower(gettype($value));
        
        if(is_array($value) && is_callable($value)) {
            $this->type = self::TYPE_CALLABLE;
        }
        
        $this->string = $this->_toString();
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
        $type = str_replace(' ', '_', $this->type);
        $varMethod = 'toString_'.$type;
        return $this->$varMethod();
    }
    
    protected function _toHTML() : string
    {
        $type = str_replace(' ', '_', $this->type);
        $varMethod = 'toHTML_'.$type;
        return $this->$varMethod();
    }
    
    protected function toFormat(string $format)
    {
        $type = str_replace(' ', '_', $this->type);
        $varMethod = 'to'.$format.'_'.$type;
        
        $converted = $this->$varMethod();
        
        if($format === 'HTML')
        {
            $converted = '<span style="color:#'.self::$colors[$type].'" class="variable-value-'.$this->type.'">'.$converted.'</span>';
        }
        
        if($this->getOption('prepend-type') === true && !$this->isNull()) 
        {
            $typeLabel = $type;
            
            switch($format)
            {
                case 'HTML':
                    $typeLabel = '<span style="color:#1c2eb1" class="variable-type">'.$type.'</span> ';
                    break;
                    
                case 'String':
                    $typeLabel = $type;
                    break;
            }
            
            $converted = $typeLabel.' '.$converted;
        }
        
        return $converted;
    }
    
   /**
    * Converts an array to a string.
    * @return string
    * 
    * @todo Create custom dump implementation, using VariableInfo instances.
    */
    protected function toString_array() : string
    {
        $result = json_encode($this->value, JSON_PRETTY_PRINT);
        
        // the array may not be encodable - for example if it contains
        // broken unicode characters. 
        if(is_string($result) && $result !== '') {
            return $result;
        }
        
        return print_r($this->value, true);
    }
    
    protected function toString_callable() : string
    {
        $string = '';
        
        if(is_string($this->value[0])) {
            $string .= $this->value[0].'::';
        } else {
            $string .= get_class($this->value[0]).'->';
        }
        
        $string .= $this->value[1].'()';
        
        return $string;
    }
    
    protected function toString_boolean() : string
    {
        return ConvertHelper::bool2string($this->value);
    }
    
    protected function toString_double() : string
    {
        return (string)$this->value;
    }
    
    protected function toString_integer() : string
    {
        return (string)$this->value;
    }
    
    protected function toString_null() : string
    {
        return 'null';
    }
    
    protected function toString_object() : string
    {
        return get_class($this->value);
    }
    
    protected function toString_resource() : string
    {
        $string = (string)$this->value;
        $string = substr($string, strpos($string, '#'));
        
        return $string;
    }
    
    protected function toString_string() : string
    {
        return $this->value;
    }
    
    protected function toString_unknown_type() : string
    {
        return 'unknown type';
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

    protected function cutString(string $string) : string
    {
        $cutAt = $this->getIntOption('cut-length', 1000);
        
        return ConvertHelper::text_cut($string, $cutAt, ' [...]');
    }
    
    public function getTypeColor() : string
    {
        return self::$colors[$this->type];
    }
    
    public function toHTML() : string
    {
        $converted = sprintf(
            '<span style="color:#%1$s" class="variable-value-%3$s">'.
                '%2$s'.
            '</span>',
            $this->getTypeColor(),
            $this->_toHTML(),
            str_replace(' ', '-', $this->type)
        );
        
        if($this->getOption('prepend-type') === true && !$this->isNull())
        {
            $typeLabel = '<span style="color:#1c2eb1" class="variable-type">'.$this->type.'</span> ';
            $converted = $typeLabel.' '.$converted;
        }
        
        return $converted;
    }
    
    protected function toHTML_integer() : string
    {
        return $this->toString();
    }
    
    protected function toHTML_array() : string
    {
        $json = $this->toString();
        $json = $this->cutString($json);
        $json = nl2br($json);
        
        return $json;
    }

    protected function toHTML_callable() : string
    {
        return $this->toString();
    }
    
    protected function toHTML_object() : string
    {
        return $this->toString();
    }
    
    protected function toHTML_resource() : string
    {
        return $this->toString();
    }
    
    protected function toHTML_string() : string
    {
        $string = $this->toString();
        $string = $this->cutString($string);
        $string = nl2br(htmlspecialchars($string));
        
        return '&quot;'.$string.'&quot;';
    }
       
    protected function toHTML_boolean() : string
    {
        return $this->toString();
    }
      
    protected function toHTML_null() : string
    {
        return $this->toString();
    }
    
    protected function toHTML_unknown_type() : string
    {
        return $this->toString();
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