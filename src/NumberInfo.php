<?php
/**
 * File containing the {@link NumberInfo} class
 *
 * @access public
 * @package Application Utils
 * @subpackage NumberInfo
 * @see NumberInfo
 */

namespace AppUtils;

/**
 * Abstraction class for numeric values for elements: offers
 * an easy to use API to work with pixel values, percentual
 * values and the like.
 *
 * Usage: use the global function {@link parseNumber()} to
 * create a new instance of the class, then use the API to
 * work with it.
 *
 * Examples:
 *
 * <pre>
 * parseNumber(42);
 * parseNumber('15%');
 * parseNumber('5em');
 * </pre>
 *
 * Hint: {@link parseNumber()} will also recognize number info
 * instances, so you can safely pass an existing number
 * info to it.
 *
 * @access public
 * @package Application Utils
 * @subpackage NumberInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class NumberInfo
{
   /**
    * @var mixed
    */
    protected $rawValue;
    
   /**
    * @var array
    */
    protected $info;
    
   /**
    * @var bool
    */
    protected $empty = false;
    
   /**
    * @var array
    */
    protected $knownUnits = array(
        '%' => true,
        'rem' => true,
        'px' => false,
        'em' => true,
        'pt' => true,
        'vw' => true,
        'vh' => true,
        'ex' => true,
        'cm' => true,
        'mm' => true,
        'in' => true,
        'pc' => true
    );
    
    public function __construct($value)
    {
        $this->setValue($value);
    }
    
    /**
     * Sets the value of the number, including the units.
     *
     * @param string|NumberInfo $value e.g. "10", "45px", "100%", ... or an existing NumberInfo instance.
     * @return NumberInfo
     */
    public function setValue($value) : NumberInfo
    {
        if($value instanceof NumberInfo) {
            $value = $value->getValue();
        }
        
        $this->rawValue = $value;
        $this->info = $this->parseValue($value);
        $this->empty = $this->info['empty'];
        
        return $this;
    }
    
   /**
    * Retrieves the raw, internal information array resulting
    * from the parsing of the number.
    *  
    * @return array
    */
    public function getRawInfo() : array
    {
        return $this->info;
    }
    
   /**
    * Whether the number was empty (null or empty string).
    * @return boolean
    */
    public function isEmpty() : bool
    {
        return $this->empty;
    }
    
    public function isPositive() : bool
    {
        if(!$this->isEmpty()) {
            $number = $this->getNumber();
            return $number > 0;
        }
        
        return false;
    }
    
    
    /**
     * Whether the number is 0.
     * @return boolean
     */
    public function isZero() : bool
    {
        return $this->getNumber() === 0;
    }
    
    public function isZeroOrEmpty() : bool
    {
        return $this->isEmpty() || $this->isZero();
    }
    
    /**
     * Whether the number has a value: this is true if
     * it is not empty, and has a non-zero value.
     *
     * @return boolean
     */
    public function hasValue() : bool
    {
        if(!$this->isEmpty() && !$this->isZero()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Whether the value is negative.
     * @return boolean
     */
    public function isNegative() : bool
    {
        return !$this->isEmpty() && $this->getNumber() < 0;
    }
    
    /**
     * Changes the stored number, without modifying the units.
     * @param int $number
     * @return NumberInfo
     */
    public function setNumber($number)
    {
        $this->info['number'] = $number;
        return $this;
    }
    
    /**
     * Whether the number is a pixel value. This is true
     * also if the px suffix is omitted.
     * @return boolean
     */
    public function isPixels()
    {
        return !$this->isEmpty() && $this->getUnits() == 'px';
    }
    
    /**
     * Whether the number is a percent value.
     * @return boolean
     */
    public function isPercent()
    {
        return !$this->isEmpty() && $this->getUnits() == '%';
    }
    
    /**
     * Retrieves the numeric value without units.
     * @return mixed
     */
    public function getNumber()
    {
        return $this->info['number'];
    }
    
    /**
     * Checks whether the number is an even number.
     * @return boolean
     */
    public function isEven()
    {
        return !$this->isEmpty() && !($this->getNumber() & 1);
    }
    
    /**
     * Retrieves the units of the number. If no units
     * have been initially specified, this will always
     * return 'px'.
     *
     * @return mixed
     */
    public function getUnits()
    {
        if(!$this->hasUnits()) {
            return 'px';
        }
        
        return $this->info['units'];
    }
    
    /**
     * Whether specific units have been specified for the number.
     * @return boolean
     */
    public function hasUnits()
    {
        return !empty($this->info['units']);
    }
    
    /**
     * Retrieves the raw value as is, with or without units depending on how it was given.
     * @return number
     */
    public function getValue()
    {
        return $this->rawValue;
    }
    
    /**
     * Formats the number for use in a HTML attribute. If units were
     * specified, only percent are kept. All other units like px and the
     * like are stripped.
     *
     * @return string
     */
    public function toAttribute()
    {
        if($this->isEmpty()) {
            return null;
        }
        
        if($this->isZero()) {
            return '0';
        }
        
        if($this->isPercent()) {
            return $this->getNumber().$this->getUnits();
        }
        
        return $this->getNumber();
    }
    
    /**
     * Formats the number for use in a CSS statement.
     * @return string
     */
    public function toCSS()
    {
        if($this->isEmpty()) {
            return null;
        }
        
        if($this->isZero()) {
            return '0';
        }
        
        return $this->getNumber().$this->getUnits();
    }
    
    public function __toString()
    {
        if($this->isEmpty()) {
            return '';
        }
        
        return (string)$this->getValue();
    }
    
    /**
     * Checks if this number is bigger than the specified
     * number. Note that this will always return false if
     * the numbers do not have the same units.
     *
     * @param string|number|NumberInfo $number
     * @return boolean
     */
    public function isBiggerThan($number)
    {
        $number = parseNumber($number);
        if($number->getUnits() != $this->getUnits()) {
            return false;
        }
        
        return $this->getNumber() > $number->getNumber();
    }
    
    /**
     * Checks if this number is smaller than the specified
     * number. Note that this will always return false if
     * the numbers do not have the same units.
     *
     * @param string|number|NumberInfo $number
     * @return boolean
     */
    public function isSmallerThan($number)
    {
        $number = parseNumber($number);
        if($number->getUnits() != $this->getUnits()) {
            return false;
        }
        
        return $this->getNumber() < $number->getNumber();
    }
    
    public function isBiggerEqual($number)
    {
        $number = parseNumber($number);
        if($number->getUnits() != $this->getUnits()) {
            return false;
        }
        
        return $this->getNumber() >= $number->getNumber();
    }
    
    /**
     * Adds the specified value to the current value, if
     * they are compatible - i.e. they have the same units
     * or a percentage.
     *
     * @param string|NumberInfo $value
     * @return NumberInfo
     */
    public function add($value)
    {
        if($this->isEmpty()) {
            $this->setValue($value);
            return $this;
        }
        
        $number = parseNumber($value);
        
        if($number->getUnits() == $this->getUnits() || !$number->hasUnits())
        {
            $new = $this->getNumber() + $number->getNumber();
            $this->setValue($new.$this->getUnits());
        }
        
        return $this;
    }
    
    /**
     * Subtracts the specified value from the current value, if
     * they are compatible - i.e. they have the same units, or
     * a percentage.
     *
     * @param string|NumberInfo $value
     * @return NumberInfo
     */
    public function subtract($value)
    {
        if($this->isEmpty()) {
            $this->setValue($value);
            return $this;
        }
        
        $number = parseNumber($value);
        
        if($number->getUnits() == $this->getUnits() || !$number->hasUnits())
        {
            $new = $this->getNumber() - $number->getNumber();
            $this->setValue($new.$this->getUnits());
        }
        
        return $this;
    }
    
    public function subtractPercent($percent)
    {
        return $this->percentOperation('-', $percent);
    }
    
    /**
     * Increases the current value by the specified percent amount.
     *
     * @param number $percent
     * @return NumberInfo
     */
    public function addPercent($percent)
    {
        return $this->percentOperation('+', $percent);
    }
    
    protected function percentOperation($operation, $percent)
    {
        if($this->isZeroOrEmpty()) {
            return $this;
        }
        
        $percent = parseNumber($percent);
        if($percent->hasUnits() && !$percent->isPercent()) {
            return $this;
        }
        
        $number = $this->getNumber();
        $value = $number * $percent->getNumber() / 100;
        
        if($operation == '-') {
            $number = $number - $value;
        } else {
            $number = $number + $value;
        }
        
        if($this->isUnitInteger()) {
            $number = intval($number);
        }
        
        $this->setValue($number.$this->getUnits());
        
        return $this;
        
    }
    
    public function isUnitInteger()
    {
        return $this->isPixels();
    }
    
    public function isUnitDecimal()
    {
        return $this->isPercent();
    }
    
    /**
     * Returns an array with information about the number
     * and the units used with the number for use in CSS
     * style attributes or HTML attributes.
     *
     * Examples:
     *
     * 58 => array(
     *     'number' => 58,
     *     'units' => null
     * )
     *
     * 58px => array(
     *     'number' => 58,
     *     'units' => 'px'
     * )
     *
     * 20% => array(
     *     'number' => 20,
     *     'units' => '%'
     * )
     *
     * @param mixed $value
     * @return array
     */
    private function parseValue($value) : array
    {
        static $cache = array();
        
        $key = $this->createValueKey($value);

        if(array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        
        $cache[$key] = array(
            'units' => null,
            'empty' => false,
            'number' => null
        );
        
        if($key === '_EMPTY_') 
        {
            $cache[$key]['empty'] = true;
            return $cache[$key];
        }
        
        if($value === 0 || $value === '0') 
        {
            $cache[$key]['number'] = 0;
            $cache[$key] = $this->filterInfo($cache[$key]);
            return $cache[$key];
        }
        
        $test = trim((string)$value);
        
        if($test === '') 
        {
            $cache[$key]['empty'] = true;
            return $cache[$key];
        }
        
        // replace comma notation (which is only possible if it's a string)
        if(is_string($value))
        {
            $test = $this->preProcess($test, $cache, $value);
        }
        
        // convert to a number if it's numeric
        if(is_numeric($test)) 
        {
            $cache[$key]['number'] = $test * 1;
            $cache[$key] = $this->filterInfo($cache[$key]);
            return $cache[$key];
        }
        
        // not numeric: there are possibly units specified in the string
        $cache[$key] = $this->parseStringValue($test);
        
        return $cache[$key];
    }
    
   /**
    * Parses a string number notation with units included, e.g. 14px, 50%...
    * 
    * @param string $test
    * @return array
    */
    private function parseStringValue(string $test) : array
    {
        $number = null;
        $units = null;
        $empty = false;
        
        $found = $this->findUnits($test);
        if($found !== null) 
        {
            $number = $found['number'];
            $units = $found['units'];
        }
        
        // the filters have to restore the value
        if($this->postProcess)
        {
            $number = $this->postProcess($number, $test);
        }
        // empty number
        else if($number === '' || $number === null || is_bool($number))
        {
            $number = null;
            $empty = true;
        }
        // found a number
        else
        {
            $number = trim($number);
            
            // may be an arbitrary string in some cases
            if(!is_numeric($number))
            {
                $number = null;
                $empty = true;
            }
            else
            {
                $number = $number * 1;
            }
        }
        
        $result = array(
            'units' => $units,
            'number' => $number,
            'empty' => $empty
        );

        return $this->filterInfo($result);
    }
    
   /**
    * Attempts to determine what kind of units are specified
    * in the string. Returns NULL if none could be matched.
    * 
    * @param string $value
    * @return array|NULL
    */
    private function findUnits(string $value) : ?array
    {
        $vlength = strlen($value);
        $names = array_keys($this->knownUnits);
        
        foreach($names as $unit)
        {
            $ulength = strlen($unit);
            $start = $vlength-$ulength;
            if($start < 0) {
                continue;
            }
            
            $search = substr($value, $start, $ulength);
            
            if($search==$unit) 
            {
                return array(
                    'units' => $unit,
                    'number' => substr($value, 0, $start)
                );
            }
        }
        
        return null;
    }
    
   /**
    * Creates the cache key for the specified value.
    * 
    * @param mixed $value
    * @return string
    */
    private function createValueKey($value) : string
    {
        if(!is_string($value) && !is_numeric($value))
        {
            return '_EMPTY_';
        }

        return (string)$value;
    }
    
    protected $postProcess = false;
    
   /**
    * Called if explicitly enabled: allows filtering the 
    * number after the detection process has completed.
    * 
    * @param string|NULL $number The adjusted number
    * @param string $originalString The original value before it was parsed
    * @return mixed
    */
    protected function postProcess(?string $number, /** @scrutinizer ignore-unused */ string $originalString)
    {
        return $number;
    }
    
   /**
    * Filters the value before it is parsed, but only if it is a string.
    * 
    * NOTE: This may be overwritten in a subclass, to allow custom filtering
    * the the values. An example of a use case would be a preprocessor for
    * variables in a templating system.
    * 
    * @param string $trimmedString The trimmed value.
    * @param array $cache The internal values cache array.
    * @param string $originalValue The original value that the NumberInfo was created for.
    * @return string
    * 
    * @see NumberInfo::enablePostProcess()
    */
    protected function preProcess(string $trimmedString, /** @scrutinizer ignore-unused */ array &$cache, /** @scrutinizer ignore-unused */ string $originalValue) : string
    {
        return str_replace(',', '.', $trimmedString);
    }
    
   /**
    * Enables the post processing so the postProcess method gets called.
    * This should be called in the {@link NumberInfo::preProcess()}
    * method as needed.
    * 
    * @return NumberInfo
    * @see NumberInfo::postProcess()
    */
    private function enablePostProcess() : NumberInfo
    {
        $this->postProcess = true;
        return $this;
    }
    
   /**
    * Filters the number info array to adjust the units
    * and number according to the required rules.
    * 
    * @param array $info
    * @return array
    */
    protected function filterInfo(array $info) : array
    {
        $useUnits = 'px';
        if($info['units'] !== null) {
            $useUnits = $info['units'];
        }
        
        // the units are non-decimal: convert decimal values
        if($useUnits !== null && $this->knownUnits[$useUnits] === false && !$info['empty'] && is_numeric($info['number']))
        {
            $info['number'] = intval($info['number']);
        }
        
        return $info;
    }
}
