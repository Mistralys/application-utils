<?php
/**
 * File containing the {@link NumberInfo} class
 *
 * @access public
 * @package Application Utils
 * @subpackage Misc
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
 * @subpackage Misc
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
class NumberInfo
{
    protected $rawValue;
    
    protected $info;
    
    protected $empty = false;
    
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
     * @param string $value e.g. "10", "45px", "100%", ...
     * @return NumberInfo
     */
    public function setValue($value)
    {
        if($value instanceof NumberInfo) {
            $value = $value->getValue();
        }
        
        $this->rawValue = $value;
        $this->info = $this->numericUnitsInfo($value);
        $this->empty = $this->info['empty'];
        
        return $this;
    }
    
   /**
    * Retrieves the raw, internal information array resulting
    * from the parsing of the number.
    *  
    * @return array
    */
    public function getRawInfo()
    {
        return $this->info;
    }
    
    /**
     * Whether the number was empty (null or empty string).
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->empty;
    }
    
    public function isPositive()
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
    public function isZero()
    {
        return $this->getNumber() === 0;
    }
    
    public function isZeroOrEmpty()
    {
        return $this->isEmpty() || $this->isZero();
    }
    
    /**
     * Whether the number has a value: this is true if
     * it is not empty, and has a non-zero value.
     *
     * @return boolean
     */
    public function hasValue()
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
    public function isNegative()
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
     * @param string $value
     * @return array
     */
    protected function numericUnitsInfo($value)
    {
        static $cache = array();
        
        $this->restoreFilters = false;
        
        if(!is_string($value) && !is_numeric($value)) {
            $value = null;
        }

        $key = (string)$value;
        
        if(array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        
        $cache[$key] = array(
            'units' => null,
            'empty' => false,
            'number' => null
        );
        
        if($value === null) {
            $cache[$key]['empty'] = true;
            return $cache[$key];
        }
        
        if($value === 0 || $value === '0') {
            $cache[$key]['number'] = 0;
            $cache[$key] = $this->filterInfo($cache[$key]);
            return $cache[$key];
        }
        
        $test = trim($value);
        if($test === '') {
            $cache[$key]['empty'] = true;
            return $cache[$key];
        }
        
        // replace comma notation (which is only possible if it's a string)
        if(is_string($test))
        {
            $test = $this->preProcess($test, $cache, $value);
        }
        
        // convert to a number if it's numeric
        if(is_numeric($test)) {
            $cache[$key]['number'] = $test * 1;
            $cache[$key] = $this->filterInfo($cache[$key]);
            return $cache[$key];
        }
        
        // not numeric: there are possibly units specified in the string
        
        $number = null;
        $units = null;
        
        $vlength = strlen($test);
        $names = array_keys($this->knownUnits);
        foreach($names as $unit)
        {
            $ulength = strlen($unit);
            $start = $vlength-$ulength;
            if($start < 0) {
                continue;
            }
            
            $search = substr($test, $start, $ulength);
            if($search==$unit) {
                $units = $unit;
                $number = substr($test, 0, $start);
                break;
            }
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
            $cache[$key]['empty'] = true;
        }
        // found a number
        else
        {
            $number = trim($number);
            
            // may be an arbitrary string in some cases
            if(!is_numeric($number))
            {
                $number = null;
                $cache[$key]['empty'] = true;
            }
            else
            {
                $number = $number * 1;
            }
        }
        
        $cache[$key]['units'] = $units;
        $cache[$key]['number'] = $number;
        
        $cache[$key] = $this->filterInfo($cache[$key]);
        
        return $cache[$key];
    }
    
    protected $postProcess = false;
    
   /**
    * Called if explicitly enabled: allows filtering the 
    * number after the detection process has completed.
    * 
    * @param mixed $number The adjusted number
    * @param string $originalString The original value before it was parsed
    * @return mixed
    */
    protected function postProcess($number, $originalString)
    {
        return $number;
    }
    
   /**
    * Filters the value before it is parsed, but only if it is a string.
    * 
    * @param string $string
    * @param array $cache
    * @return mixed
    */
    protected function preProcess(string $string, &$cache, $value)
    {
        return str_replace(',', '.', $string);
    }
    
   /**
    * Enables the post processing so the postProcess method gets called.
    * This should be called in the {@link NumberInfo::preProcess()}
    * method as needed.
    * 
    * @return NumberInfo
    * @see NumberInfo::postProcess()
    */
    protected function enablePostProcess() : NumberInfo
    {
        $this->postProcess = true;
        return $this;
    }
    
    protected function filterInfo($info)
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
