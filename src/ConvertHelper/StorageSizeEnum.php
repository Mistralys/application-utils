<?php
/**
 * File containing the {@see ConvertHelper_StorageSizeEnum} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see ConvertHelper_StorageSizeEnum
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Static class used to handle possible storage sizes,
 * like Kilobytes, Megabytes and the like. Offers an easy-to-use
 * interface to access information on these sizes,
 * and to translate their labels to the application locale. 
 * 
 * It supports both Base 10 and Base 2 sizes.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_StorageSizeEnum
{
    public const ERROR_UNKNOWN_UNIT_NAME = 43901;
    
    public const BASE_10 = 1000;
    public const BASE_2 = 1024;
    
   /**
    * @var array<string,ConvertHelper_StorageSizeEnum_Size>
    */
    protected static $sizes = array();
    
   /**
    * Initializes the supported unit notations, and
    * how they are supposed to be calculated.
    *
    * @see ConvertHelper_SizeNotation::parseSize()
    */
    protected static function init() : void
    {
        if(!empty(self::$sizes)) {
            return;
        }
        
        self::addSize('kib', self::BASE_2, 1, t('KiB'), t('Kibibyte'), t('Kibibytes')); 
        self::addSize('mib', self::BASE_2, 2, t('MiB'), t('Mebibyte'), t('Mebibytes'));
        self::addSize('gib', self::BASE_2, 3, t('GiB'), t('Gibibyte'), t('Gibibytes'));
        self::addSize('tib', self::BASE_2, 4, t('TiB'), t('Tebibyte'), t('Tebibytes'));
        self::addSize('pib', self::BASE_2, 5, t('PiB'), t('Pebibyte'), t('Pebibytes'));

        self::addSize('kb', self::BASE_10, 1, t('KB'), t('Kilobyte'), t('Kilobytes'));
        self::addSize('mb', self::BASE_10, 2, t('MB'), t('Megabyte'), t('Megabytes'));
        self::addSize('gb', self::BASE_10, 3, t('GB'), t('Gigabyte'), t('Gigabytes'));
        self::addSize('tb', self::BASE_10, 4, t('TB'), t('Terabyte'), t('Terabytes'));
        self::addSize('pb', self::BASE_10, 5, t('PB'), t('Petabyte'), t('Petabytes'));
        
        self::addSize('b', 1, 1, t('B'), t('Byte'), t('Bytes'));
        
        if(class_exists('AppLocalize\Localization')) 
        {
            \AppLocalize\Localization::onLocaleChanged(array(self::class, 'handle_localeChanged'));
        }
    }
    
   /**
    * Called whenever the application locale is changed,
    * to reset the size definitions so the labels get 
    * translated to the new locale.
    */
    public static function handle_localeChanged() : void
    {
        self::$sizes = array();
    }
    
   /**
    * Adds a storage size to the internal collection.
    * 
    * @param string $name The lowercase size name, e.g. "kb", "mib"
    * @param int $base This defines how many bytes there are in a kilobyte, to differentiate with the two common way to calculate sizes: base 10 or base 2. See the Wikipedia link for more details.
    * @param int $exponent The multiplier of the base to get the byte value
    * @param string $suffix The localized short suffix, e.g. "KB", "MiB"
    * @param string $singular The localized singular label of the size, e.g. "Kilobyte".
    * @param string $plural The localized plural label of the size, e.g. "Kilobytes".
    * 
    * @see https://en.m.wikipedia.org/wiki/Megabyte#Definitions
    */
    protected static function addSize(string $name, int $base, int $exponent, string $suffix, string $singular, string $plural) : void
    {
        self::$sizes[$name] = new ConvertHelper_StorageSizeEnum_Size(
            $name,
            $base,
            $exponent,
            $suffix,
            $singular,
            $plural
        );
    }
    
   /**
    * Retrieves all known sizes.
    * 
    * @return ConvertHelper_StorageSizeEnum_Size[]
    */
    public static function getSizes() : array
    {
        self::init();
        
        return array_values(self::$sizes);
    }
    
   /**
    * Retrieves a size definition instance by its name.
    * 
    * @param string $name Case-insensitive. For example "kb", "MiB"...
    * @throws ConvertHelper_Exception
    * @return ConvertHelper_StorageSizeEnum_Size
    * 
    * @see ConvertHelper_StorageSizeEnum::ERROR_UNKNOWN_UNIT_NAME
    */
    public static function getSizeByName(string $name) : ConvertHelper_StorageSizeEnum_Size
    {
        self::init();
        
        $name = strtolower($name);
        
        if(isset(self::$sizes[$name])) {
            return self::$sizes[$name];
        }
        
        throw new ConvertHelper_Exception(
            'Unknown storage size name '.$name.'.',
            sprintf(
                'The storage size name [%s] does not exist. Available names are: [%s].',
                $name,
                implode(', ', self::getSizeNames())
            ),
            self::ERROR_UNKNOWN_UNIT_NAME
        );
    }
    
   /**
    * Retrieves a list of all size names, e.g. "mb", "kib" (lowercase).
    * @return string[]
    */
    public static function getSizeNames() : array
    {
        self::init();
        
        return array_keys(self::$sizes);
    }
   
   /**
    * Retrieves all available storage sizes for the specified
    * base value.
    * 
    * @param int $base
    * @return ConvertHelper_StorageSizeEnum_Size[]
    * 
    * @see ConvertHelper_StorageSizeEnum::BASE_10
    * @see ConvertHelper_StorageSizeEnum::BASE_2
    */
    public static function getSizesByBase(int $base) : array
    {
        self::init();
        
        $result = array();
        
        foreach(self::$sizes as $size)
        {
            if($size->getBase() === $base) {
                $result[] = $size;
            }
        }
        
        return $result;
    }
}
