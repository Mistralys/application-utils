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
 * like Kilobytes, Megabytes and the like. Offers an easy
 * to use interface to access information on these sizes,
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
    const ERROR_UNKNOWN_UNIT_NAME = 43901;
    
    const BASE_10 = 1000;
    
    const BASE_2 = 1024;
    
   /**
    * @var ConvertHelper_StorageSizeEnum_Size[]|NULL
    */
    protected static $sizes = null;
    
   /**
    * Initializes the supported unit notations, and
    * how they are supposed to be calculated.
    *
    * @see ConvertHelper_SizeNotation::parseSize()
    */
    protected static function init()
    {
        if(isset(self::$sizes)) {
            return;
        }
        
        self::$sizes = array();
        
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
    * 
    * @param \AppLocalize\Localization_Event_LocaleChanged $event
    */
    public static function handle_localeChanged(\AppLocalize\Localization_Event_LocaleChanged $event)
    {
        self::$sizes = null;
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
    protected static function addSize(string $name, int $base, int $exponent, string $suffix, string $singular, string $plural)
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
    * @return \AppUtils\ConvertHelper_StorageSizeEnum_Size[]
    */
    public static function getSizes()
    {
        self::init();
        
        return self::$sizes;
    }
    
   /**
    * Retrieves a size definition instance by its name.
    * 
    * @param string $name Case insensitive. For example "kb", "MiB"...
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
                'The storage size name [%s] does not exist. Avaialable names are: [%s].',
                $name,
                implode(', ', self::getSizeNames())
            ),
            self::ERROR_UNKNOWN_UNIT_NAME
        );
    }
    
   /**
    * Retrieves a list of all size names, e.g. "mb", "kib" (lowercase).
    * @return array
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
    * @return \AppUtils\ConvertHelper_StorageSizeEnum_Size[]
    * 
    * @see ConvertHelper_StorageSizeEnum::BASE_10
    * @see ConvertHelper_StorageSizeEnum::BASE_2
    */
    public static function getSizesByBase(int $base)
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
