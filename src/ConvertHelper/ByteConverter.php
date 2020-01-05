<?php
/**
 * File containing the {@see ConvertHelper_ByteConverter} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see ConvertHelper_ByteConverter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * This class is made to convert an amount of bytes into a storage 
 * size notation like "50 MB".
 *
 * It supports both Base 10 and Base 2 size calculation.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_ByteConverter
{
   /**
    * @var int
    */
    protected $bytes;
    
    public function __construct(int $bytes)
    {
        $this->bytes = $bytes;
        
        // correct negative values
        if($this->bytes < 0) 
        {
            $this->bytes = 0;
        }
    }
    
   /**
    * Detects the size matching the byte value for the specified base.
    * 
    * @param int $base
    * @return ConvertHelper_StorageSizeEnum_Size
    */
    protected function detectSize(int $base) : ConvertHelper_StorageSizeEnum_Size
    {
        $sizes = $this->getSizesSorted($base);   

        if($this->bytes >= $sizes[0]->getBytes()) 
        {
            $total = count($sizes);
            
            for($i=0; $i < $total; $i++)
            {
                $size = $sizes[$i];
                
                if(!isset($sizes[($i+1)])) {
                    return $size;
                }
                
                if($this->bytes >= $size->getBytes() && $this->bytes < $sizes[($i+1)]->getBytes()) {
                    return $size;
                }
            }
        }
        
        return ConvertHelper_StorageSizeEnum::getSizeByName('b');
    }
    
   /**
    * Retrieves all storage sizes for the specified base, 
    * sorted from smallest byte size to highest.
    * 
    * @param int $base
    * @return \AppUtils\ConvertHelper_StorageSizeEnum_Size[]
    */
    protected function getSizesSorted(int $base)
    {
        $sizes = ConvertHelper_StorageSizeEnum::getSizesByBase($base);
        
        usort($sizes, function(ConvertHelper_StorageSizeEnum_Size $a, ConvertHelper_StorageSizeEnum_Size $b)
        {
            return $a->getBytes() - $b->getBytes();
        });
        
        return $sizes;
    }
    
   /**
    * Converts the byte value to a human readable string, e.g. "5 KB", "140 MB".
    * 
    * @param int $precision The amount of decimals (rounded up)
    * @param int $base The base to calculate bytes with.
    * @return string
    * 
    * @see ConvertHelper_StorageSizeEnum::BASE_10
    * @see ConvertHelper_StorageSizeEnum::BASE_2
    */
    public function toString(int $precision, int $base=ConvertHelper_StorageSizeEnum::BASE_10) : string
    {
        $size = $this->detectSize($base);
        
        return round($this->bytes / $size->getBytes(), $precision) . ' ' . $size->getSuffix();
    }
    
   /**
    * Converts the byte value to the amount of the corresponding units (KB, MB...).
    * 
    * @param int $precision The amount of decimals (rounded up)
    * @param string $sizeName The lowercase storage size name (e.g. "kb", "kib")
    * @return float
    */
    public function toNumber(int $precision, string $sizeName) : float
    {
        $size = ConvertHelper_StorageSizeEnum::getSizeByName($sizeName);
        
        return round($this->bytes / $size->getBytes(), $precision);
    }
    
   /**
    * Converts the bytes to Kilobytes.
    * 
    * @param int $precision Amount of decimals (rounded up)
    * @return float
    */
    public function toKilobytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'kb');
    }
    
   /**
    * Converts the bytes to Megabytes.
    *
    * @param int $precision Amount of decimals (rounded up)
    * @return float
    */
    public function toMegabytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'mb');
    }

   /**
    * Converts the bytes to Gigabytes.
    *
    * @param int $precision Amount of decimals (rounded up)
    * @return float
    */
    public function toGigabytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'gb');
    }

   /**
    * Converts the bytes to Terabytes.
    *
    * @param int $precision Amount of decimals (rounded up)
    * @return float
    */
    public function toTerabytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'tb');
    }
    
   /**
    * Converts the bytes to Petabytes.
    *
    * @param int $precision Amount of decimals (rounded up)
    * @return float
    */
    public function toPetabytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'pb');
    }

    /**
     * Converts the bytes to Kibibytes (Base 2).
     *
     * @param int $precision Amount of decimals (rounded up)
     * @return float
     */
    public function toKibibytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'kib');
    }
    
    /**
     * Converts the bytes to Mebibytes (Base 2).
     *
     * @param int $precision Amount of decimals (rounded up)
     * @return float
     */
    public function toMebibytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'mib');
    }
    
    /**
     * Converts the bytes to Gibibytes (Base 2).
     *
     * @param int $precision Amount of decimals (rounded up)
     * @return float
     */
    public function toGibibytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'gib');
    }
    
    /**
     * Converts the bytes to Tebibytes (Base 2).
     *
     * @param int $precision Amount of decimals (rounded up)
     * @return float
     */
    public function toTebibytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'tib');
    }
    
    /**
     * Converts the bytes to Pebibytes (Base 2).
     *
     * @param int $precision Amount of decimals (rounded up)
     * @return float
     */
    public function toPebibytes(int $precision=1) : float
    {
        return $this->toNumber($precision, 'pib');
    }
}
