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
    * @var array
    */
    protected $value;
    
   /**
    * @var string
    */
    protected $type;
    
   /**
    * @param mixed $value
    */
    public function __construct($value)
    {
        $this->value = $value;
        $this->type = strtolower(gettype($value));
        
        if(is_array($value) && is_callable($value)) {
            $this->type = self::TYPE_CALLABLE;
        }
    }
    
    public function getType()
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
        return $this->toFormat('String');
    }
    
    protected function toFormat(string $format)
    {
        $type = $this->type;
        
        if($this->type === self::TYPE_UNKNOWN) {
            $type = 'unknown';
        }
        
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
    
    protected function toString_array() : string
    {
        return json_encode($this->value, JSON_PRETTY_PRINT);
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
        if($this->getOption('prepend-type')) {
            return '"'.$this->value.'"';
        }
        
        return $this->value;
    }
    
    protected function toString_unknown() : string
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

    protected function cutString($string)
    {
        $cutAt = $this->getOption('cut-length');
        
        return ConvertHelper::text_cut($string, $cutAt, ' [...]');
    }
    
    public function toHTML() : string
    {
        return $this->toFormat('HTML');
    }
    
    protected function toHTML_integer() : string
    {
        return $this->toString_integer();
    }
    
    protected function toHTML_array() : string
    {
        $json = $this->toString_array();
        $json = $this->cutString($json);
        $json = nl2br($json);
        
        return $json;
    }

    protected function toHTML_callable() : string
    {
        return $this->toString_object();
    }
    
    protected function toHTML_object() : string
    {
        return $this->toString_object();
    }
    
    protected function toHTML_resource() : string
    {
        return $this->toString_resource();
    }
    
    protected function toHTML_string() : string
    {
        $string = $this->toString_string();
        $string = $this->cutString($string);
        $string = nl2br(htmlspecialchars($string));
        
        return '&quot;'.$string.'&quot;';
    }
       
    protected function toHTML_boolean() : string
    {
        return $this->toString_boolean();
    }
      
    protected function toHTML_null() : string
    {
        return $this->toString_null();
    }
    
    protected function toHTML_unknown() : string
    {
        return $this->toString_unknown();
    }
}