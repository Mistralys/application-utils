<?php
/**
 * File containing the {@link NumberInfo} class
 *
 * @access public
 * @package Application Utils
 * @subpackage NumberInfo
 * @see NumberInfo
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Abstraction class for numeric values for elements: offers
 * an easy-to-use API to work with pixel values, percentage
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
    * @var string|int|float|null
    */
    protected $rawValue;
    
   /**
    * @var array<string,mixed>
    */
    protected array $info;
    
   /**
    * @var bool
    */
    protected bool $empty = false;

    /**
     * @var bool
     */
    protected bool $postProcess = false;

    /**
     * Units and whether they allow decimal values.
    * @var array<string,bool>
    */
    protected array $knownUnits = array(
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

    /**
     * @var int
     */
    private static int $instanceCounter = 0;

    /**
     * @var int
     */
    protected int $instanceID;

    /**
     * @param string|int|float|NumberInfo|NULL $value
     */
    public function __construct($value)
    {
        self::$instanceCounter++;
        $this->instanceID = self::$instanceCounter;

        $this->_setValue($value);
    }

    /**
     * Gets the ID of this NumberInfo instance: every unique
     * instance gets assigned an ID to be able to distinguish
     * them (mainly used in the unit tests, but also has a few
     * practical applications).
     *
     * @return int
     */
    public function getInstanceID() : int
    {
        return $this->instanceID;
    }

    /**
     * Sets the value of the number, including the units.
     *
     * @param string|int|float|NumberInfo|NULL $value e.g. "10", "45px", "100%", ... or an existing NumberInfo instance.
     * @return $this
     */
    public function setValue($value)
    {
        return $this->_setValue($value);
    }

    /**
     * @param string|int|float|NumberInfo|NULL $value
     * @return $this
     */
    protected function _setValue($value)
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
    * @return array<string,mixed>
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

    /**
     * Whether the number is bigger than 0.
     *
     * NOTE: Empty numbers (NULL) will always return false.
     *
     * @return bool
     */
    public function isPositive() : bool
    {
        if($this->isEmpty())
        {
            return false;
        }

        return $this->getNumber() > 0;
    }

    /**
     * Whether the number is exactly `0`.
     *
     * @return boolean
     */
    public function isZero() : bool
    {
        if($this->isEmpty())
        {
            return false;
        }

        return (float)$this->getNumber() === 0.0;
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
        return !$this->isEmpty() && !$this->isZero();
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
     * Changes the stored number.
     *
     * NOTE: Will be ignored if the specified number's
     * units do not match.
     *
     * @param string|int|float|NULL|NumberInfo $number
     * @return $this
     */
    public function setNumber($number)
    {
        // Append the units if the value is a number,
        // so they can be inherited.
        if($this->hasUnits() && is_numeric($number))
        {
            $number .= $this->getUnits();
        }

        $new = parseNumber($number);

        if($new->isEmpty())
        {
            return $this;
        }

        if($new->getUnits() === $this->getUnits())
        {
            $value = $new->getNumber();

            if($this->hasUnits()) {
                $value .= $this->getUnits();
            }

            $this->_setValue($value);
        }

        return $this;
    }
    
    /**
     * Whether the number is a pixel value. This is true
     * also if the px suffix is omitted.
     * @return boolean
     */
    public function isPixels() : bool
    {
        return !$this->isEmpty() && $this->getUnits() === 'px';
    }
    
    /**
     * Whether the number is a percent value.
     * @return boolean
     */
    public function isPercent() : bool
    {
        return !$this->isEmpty() && $this->getUnits() === '%';
    }

    public function isEM() : bool
    {
        return !$this->isEmpty() && $this->getUnits() === 'em';
    }
    
    /**
     * Retrieves the numeric value without units.
     * @return float|int
     */
    public function getNumber()
    {
        $number = (float)$this->info['number'];

        if($this->hasDecimals())
        {
            return $number;
        }

        return intval($number);
    }

    public function hasDecimals() : bool
    {
        $number = (float)$this->info['number'];

        return floor($number) !== $number;
    }
    
    /**
     * Checks whether the number is an even number.
     * @return boolean
     */
    public function isEven() : bool
    {
        return !$this->isEmpty() && !($this->getNumber() & 1);
    }
    
    /**
     * Retrieves the units of the number. If no units
     * have been initially specified, this will always
     * return 'px'.
     *
     * NOTE: If the number itself is empty (NULL), this
     * will return an empty string.
     *
     * @return string
     */
    public function getUnits() : string
    {
        if($this->isEmpty()) {
            return '';
        }

        if(!$this->hasUnits()) {
            return 'px';
        }
        
        return $this->info['units'];
    }
    
    /**
     * Whether specific units have been specified for the number.
     * @return boolean
     */
    public function hasUnits() : bool
    {
        return !empty($this->info['units']);
    }
    
    /**
     * Retrieves the raw value as is, with or without units depending on how it was given.
     * @return string|int|float|NULL
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
    public function toAttribute() : string
    {
        if($this->isEmpty()) {
            return '';
        }
        
        if($this->isZero()) {
            return '0';
        }
        
        if($this->isPercent()) {
            return $this->getNumber().$this->getUnits();
        }
        
        return (string)$this->getNumber();
    }
    
    /**
     * Formats the number for use in a CSS statement.
     * @return string
     */
    public function toCSS() : string
    {
        if($this->isEmpty()) {
            return '';
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
     * number.
     *
     * NOTE: Always returns false if the units are not the same.
     *
     * NOTE: If this number or the one being compared is empty
     * (NULL), this will return false even if it translates
     * to a `0` value.
     *
     * @param string|int|float|NULL|NumberInfo $number
     * @return boolean
     */
    public function isBiggerThan($number) : bool
    {
        return (new NumberInfo_Comparer($this, parseNumber($number)))->isBiggerThan();
    }
    
    /**
     * Checks if this number is smaller than the specified
     * number.
     *
     * NOTE: Always returns false if the units are not the same.
     *
     * NOTE: If this number or the one being compared is empty
     * (NULL), this will return false even if it translates
     * to a `0` value.
     *
     * @param string|int|float|NULL|NumberInfo $number
     * @return boolean
     */
    public function isSmallerThan($number) : bool
    {
        return (new NumberInfo_Comparer($this, parseNumber($number)))->isSmallerThan();
    }

    /**
     * Checks if this number is smaller than the specified
     * number.
     *
     * NOTE: Always returns false if the units are not the same.
     *
     * NOTE: If this number or the one being compared is empty
     * (NULL), this will return false even if it translates
     * to a `0` value.
     *
     * @param string|int|float|NULL|NumberInfo $number
     * @return boolean
     */
    public function isSmallerEqual($number) : bool
    {
        return (new NumberInfo_Comparer($this, parseNumber($number)))->isSmallerEqual();
    }

    /**
     * Checks if this number is bigger or equals the
     * specified number.
     *
     * NOTE: Always returns false if the units are not the same.
     *
     * NOTE: If this number or the one being compared is empty
     * (NULL), this will return false even if it translates
     * to a `0` value.
     *
     * @param string|int|float|NULL|NumberInfo $number
     * @return bool
     */
    public function isBiggerEqual($number) : bool
    {
        return (new NumberInfo_Comparer($this, parseNumber($number)))->isBiggerEqual();
    }

    /**
     * Checks if this number equals the specified number.
     *
     * NOTE: Always returns false if the units are not the same.
     *
     * NOTE: If this number or the one being compared is empty
     * (NULL), this will return false even if it translates
     * to a `0` value.
     *
     * @param string|int|float|NULL|NumberInfo $number
     * @return bool
     */
    public function isEqual($number) : bool
    {
        return (new NumberInfo_Comparer($this, parseNumber($number)))->isEqual();
    }
    
    /**
     * Adds the specified value to the current value, if
     * they are compatible - i.e. they have the same units
     * or a percentage.
     *
     * @param string|int|float|null|NumberInfo $value
     * @return $this
     */
    public function add($value)
    {
        if($this->isEmpty())
        {
            $this->setValue($value);
            return $this;
        }
        
        $number = parseNumber($value);
        
        if($number->getUnits() === $this->getUnits() || !$number->hasUnits())
        {
            $new = $this->getNumber() + $number->getNumber();

            if($this->hasUnits())
            {
                $new .= $this->getUnits();
            }

            $this->setValue($new);
        }
        
        return $this;
    }
    
    /**
     * Subtracts the specified value from the current value, if
     * they are compatible - i.e. they have the same units, or
     * a percentage.
     *
     * @param string|int|float|NumberInfo|NULL $value
     * @return $this
     */
    public function subtract($value)
    {
        if($this->isEmpty())
        {
            $this->setValue($value);
            return $this;
        }
        
        $number = parseNumber($value);
        
        if($number->getUnits() == $this->getUnits() || !$number->hasUnits())
        {
            $new = $this->getNumber() - $number->getNumber();

            if($this->hasUnits())
            {
                $new .= $this->getUnits();
            }

            $this->setValue($new);
        }
        
        return $this;
    }

    /**
     * Subtracts the specified percentage from the number.
     *
     * @param float $percent
     * @return $this
     */
    public function subtractPercent(float $percent)
    {
        return $this->percentOperation('-', $percent);
    }
    
    /**
     * Increases the current value by the specified percent amount.
     *
     * @param float $percent
     * @return $this
     */
    public function addPercent(float $percent)
    {
        return $this->percentOperation('+', $percent);
    }

    /**
     * @param string $operation
     * @param int|float $percent
     * @return $this
     */
    protected function percentOperation(string $operation, $percent)
    {
        if($this->isZeroOrEmpty()) {
            return $this;
        }
        
        $percent = parseNumber($percent);

        if($percent->hasUnits() && !$percent->isPercent())
        {
            return $this;
        }
        
        $number = $this->getNumber();
        $value = $number * $percent->getNumber() / 100;
        
        if($operation == '-') {
            $number = $number - $value;
        } else {
            $number = $number + $value;
        }
        
        if($this->isUnitInteger())
        {
            $number = intval($number);
        }

        if($this->hasUnits())
        {
            $number .= $this->getUnits();
        }

        $this->setValue($number);
        
        return $this;
    }
    
    public function isUnitInteger() : bool
    {
        $units = $this->getUnits();

        if(isset($this->knownUnits[$units]))
        {
            return !$this->knownUnits[$units];
        }

        return false;
    }
    
    public function isUnitDecimal() : bool
    {
        $units = $this->getUnits();

        if(isset($this->knownUnits[$units]))
        {
            return $this->knownUnits[$units];
        }

        return false;
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
     * @param string|int|float|NULL $value
     * @return array<string,mixed>
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
            $cache[$key]['number'] = (float)$test * 1;
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
    * @return array<string,mixed>
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
                $number = (float)$number * 1;
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
    * @return array<string,mixed>|NULL
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

   /**
    * Called if explicitly enabled: allows filtering the 
    * number after the detection process has completed.
    * 
    * @param string|NULL $number The adjusted number
    * @param string $originalString The original value before it was parsed
    * @return string|null
    */
    protected function postProcess(?string $number, /** @scrutinizer ignore-unused */ string $originalString)
    {
        return $number;
    }
    
   /**
    * Filters the value before it is parsed, but only if it is a string.
    * 
    * NOTE: This may be overwritten in a subclass, to allow custom filtering
    * the values. An example of a use case would be a preprocessor for
    * variables in a templating system.
    * 
    * @param string $trimmedString The trimmed value.
    * @param array<string,mixed> $cache The internal values cache array.
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
    * Enables the post-processing so the postProcess method gets called.
    * This should be called in the {@link NumberInfo::preProcess()}
    * method as needed.
    * 
    * @return $this
    * @see NumberInfo::postProcess()
    */
    protected function enablePostProcess() : NumberInfo
    {
        $this->postProcess = true;
        return $this;
    }
    
   /**
    * Filters the number info array to adjust the units
    * and number according to the required rules.
    * 
    * @param array<string,mixed> $info
    * @return array<string,mixed>
    */
    protected function filterInfo(array $info) : array
    {
        $useUnits = 'px';
        if($info['units'] !== null) {
            $useUnits = $info['units'];
        }
        
        // the units are non-decimal: convert decimal values
        if($this->knownUnits[$useUnits] === false && !$info['empty'] && is_numeric($info['number']))
        {
            $info['number'] = intval($info['number']);
        }
        
        return $info;
    }

    /**
     * Rounds fractions down in the number, and
     * decreases it to the nearest even number
     * if necessary.
     *
     * Examples:
     *
     * - 4 -> 4
     * - 5 -> 4
     * - 5.2 -> 4
     * - 5.8 -> 4
     *
     * @return $this
     */
    public function floorEven()
    {
        $number = floor($this->getNumber());

        if($number % 2 == 1) $number--;

        return $this->setNumber($number);
    }

    /**
     * Rounds fractions up in the number, and
     * increases it to the nearest even number
     * if necessary.
     *
     * @return $this
     */
    public function ceilEven()
    {
        $number = ceil($this->getNumber());

        if($number % 2 == 1) $number++;

        return $this->setNumber($number);
    }
}
