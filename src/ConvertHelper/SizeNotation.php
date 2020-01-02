<?php
/**
 * File containing the {@see ConvertHelper_SizeNotation} class.
 * 
 * @package AppUtils
 * @subpackage ConvertHelper
 * @see ConvertHelper_SizeNotation
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * This class is made to parse and convert storage size notations
 * like "50 MB" into the equivalent amount of bytes, as well as 
 * additional formats. It features validation, and accessing information
 * on the error that was detected if the parsing fails.
 * 
 * It supports both Base 10 and Base 2 size calculation.
 * 
 * @package AppUtils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_SizeNotation
{
    const VALIDATION_ERROR_MULTIPLE_UNITS = 43801;
    
    const VALIDATION_ERROR_UNRECOGNIZED_STRING = 43802;
    
    const VALIDATION_ERROR_NEGATIVE_VALUE = 43803;
    
   /**
    * Stores the supported unit notations, and
    * how they are supposed to be calculated. 
    * 
    * The `base` key: This defines how many bytes there are
    * in a kilobyte, to differentiate with the two
    * common way to calculate sizes: base 10 or base 2.
    * See the Wikipedia link for more details.
    * 
    * The `exponent` key: This defines the multiplier to
    * multiply the value with to get the target size.
    * 
    * @var array
    * @see https://en.m.wikipedia.org/wiki/Megabyte#Definitions 
    * @see ConvertHelper_SizeNotation::parseSize()
    */
    protected static $unitDefs = array(
        'kib' => array(
            'base' => 1024,
            'exponent' => 1
        ),
        'mib' => array(
            'base' => 1024,
            'exponent' => 2
        ),
        'gib' => array(
            'base' => 1024,
            'exponent' => 3
        ),
        'tib' => array(
            'base' => 1024,
            'exponent' => 4
        ),
        'pib' => array(
            'base' => 1024,
            'exponent' => 5
        ),
        'kb' => array(
            'base' => 1000,
            'exponent' => 1
        ),
        'mb' => array(
            'base' => 1000,
            'exponent' => 2
        ),
        'gb' => array(
            'base' => 1000,
            'exponent' => 3
        ),
        'tb' => array(
            'base' => 1000,
            'exponent' => 4
        ),
        'pb' => array(
            'base' => 1000,
            'exponent' => 5
        ),
        'b' => array(
            'base' => 1,
            'exponent' => 1
        ),
    );
    
   /**
    * @var string
    */
    protected $size;
    
   /**
    * @var integer
    */
    protected $bytes = 0;
    
   /**
    * @var bool
    */
    protected $valid = true;
    
   /**
    * @var string
    */
    protected $units = null;
    
   /**
    * @var string
    */
    protected $number = '';
    
   /**
    * @var string
    */
    protected $errorMessage = '';
    
   /**
    * @var int
    */
    protected $errorCode = 0;
    
   /**
    * Create a new instance for the specified size string.
    * 
    * @param string $size A size notation in the format "50 MB", or a number of bytes without units. Spaces are ignored, so "50MB" = "50 MB" = "  50   MB   ". Floating point values are accepted, both with dot and comma notation ("50.5" = "50,5"). To use Base 2 values, ose appropriate units, e.g. "50 MiB", "1.5 GiB". The units are case insensitive, so "50 MB" = "50 mb". 
    */
    public function __construct(string $size)
    {
        $this->size = $this->cleanString($size);
        
        $this->convert();
    }
    
   /**
    * Gets the amount of bytes contained in the size notation.
    * @return int
    */
    public function toBytes() : int
    {
        return $this->bytes;
    }
    
   /**
    * Checks whether the size notation was valid and could be parsed
    * into a meaningful byte value. If this returns `false`, it is 
    * possible to use the `getErrorXXX` methods to retrieve information
    * on what went wrong. 
    * 
    * @return bool
    * @see ConvertHelper_SizeNotation::getErrorMessage()
    * @see ConvertHelper_SizeNotation::getErrorCode()
    */
    public function isValid() : bool
    {
        return $this->valid;
    }
    
   /**
    * Retrieves the error message if the size notation validation failed.
    * 
    * @return string
    * @see ConvertHelper_SizeNotation::getErrorCode()
    */
    public function getErrorMessage() : string
    {
        return $this->errorMessage;
    }
    
    protected function cleanString(string $string) : string
    {
        // remove spaces
        $result = trim(str_replace(' ', '', $string));
        
        // convert numeric notation with commas instead of dots
        $result = str_replace(',', '.', $result);
        
        // for case insensitivity, treat it all lowercase
        $result = strtolower($result);
        
        return $result;
    }
    
    protected function parseSize() : void
    {
        if(!$this->detectParts()) {
            return;
        }
        
        // we detected units in the string: all good.
        if($this->units !== null)
        {
            return;
        }
        
        // just a numeric value: we assume this means we're dealing with bytes.
        if(is_numeric($this->number)) 
        {
            $this->units = 'b';
            return;
        } 
        
        // no units found: this can be either a raw byte value,
        // or some other meaningless string.
        $this->setError(
            t('Unrecognized size string.'),
            self::VALIDATION_ERROR_UNRECOGNIZED_STRING
        );
    }
    
   /**
    * Detects the units and the number in the size notation string.
    * Populates the `units` and `number` properties.
    * 
    * @return bool Whether the string could be parsed successfully.
    */
    protected function detectParts() : bool
    {
        $units = array_keys(self::$unitDefs);
        
        $number = $this->size;
        
        foreach($units as $unit)
        {
            if(stristr($number, $unit))
            {
                // there are more than 1 unit defined in the string
                if($this->units !== null)
                {
                    $this->setError(
                        t(
                            'Multiple units defined in the string (%1$s and %2$s).',
                            $this->units,
                            $unit
                        ),
                        self::VALIDATION_ERROR_MULTIPLE_UNITS
                    );
                    
                    return false;
                }
                
                $this->units = $unit;
                $number = str_replace($unit, '', $number);
            }
        }
        
        $this->number = $number;
        
        return true;
    }
    
   /**
    * If the validation fails, this is used to store the reason for retrieval later.
    *  
    * @param string $message
    * @param int $code
    * 
    * @see ConvertHelper_SizeNotation::isValid()
    * @see ConvertHelper_SizeNotation::getErrorMessage()
    * @see ConvertHelper_SizeNotation::getErrorCode()
    */
    protected function setError(string $message, int $code) : void
    {
        $this->valid = false;
        $this->errorMessage = $message;
        $this->errorCode = $code;
    }
    
   /**
    * Retrieves the error code, if the size notation validation failed.
    * 
    * @return int
    * @see ConvertHelper_SizeNotation::getErrorMessage()
    */
    public function getErrorCode() : int
    {
        return $this->errorCode;
    }
    
    protected function convert() : void
    {
        $this->parseSize();
        
        if(!$this->valid) {
            return;
        }
        
        $int = intval($this->number);
        
        // negative values
        if($int < 0) 
        {
            $this->setError(
                t('Negative values cannot be used as size.'),
                self::VALIDATION_ERROR_NEGATIVE_VALUE
            );
            
            return;
        }
        
        $def = self::$unitDefs[$this->units];
        
        $this->bytes = (int)($int * ($def['base'] ** $def['exponent']));
    }
}
