<?php
/**
 * File containing the {@see ConvertHelper_StorageSizeEnum_Size} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see ConvertHelper_StorageSizeEnum_Size
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Stores information about individual storage sizes (like Megabytes, 
 * Kibibytes...), and offers utility methods to access the information.
 * 
 * NOTE: Use the enum's methods to retrieve instances of this class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see ConvertHelper_StorageSizeEnum
 */
class ConvertHelper_StorageSizeEnum_Size
{
   /**
    * @var string
    */
    protected $name;
    
   /**
    * @var int
    */
    protected $base;
    
   /**
    * @var int
    */
    protected $exponent;
    
   /**
    * @var string
    */
    protected $singular;
    
   /**
    * @var string
    */
    protected $plural;
    
   /**
    * @var string
    */
    protected $suffix;
    
    public function __construct(string $name, int $base, int $exponent, string $suffix, string $singular, string $plural)
    {
        $this->name = $name;
        $this->base = $base;
        $this->exponent = $exponent;
        $this->suffix = $suffix;
        $this->singular = $singular;
        $this->plural = $plural;
    }
    
    public function getBytes() : int
    {
        return $this->base ** $this->exponent;
    }
    
    public function getName() : string
    {
        return $this->name;
    }
    
    public function getBase() : int
    {
        return $this->base;
    }
    
    public function getExponent() : int
    {
        return $this->exponent;
    }
    
    public function getSuffix() : string
    {
        return $this->suffix;
    }

    public function getLabelSingular() : string
    {
        return $this->singular;
    }
    
    public function getLabelPlural() : string
    {
        return $this->plural;
    }
}
