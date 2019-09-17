<?php
/**
 * File containing the {@link UnitsHelper} class.
 * 
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UnitsHelper
 */

/**
 * Utility helper class for handling common unit measures,
 * localized for the application locale.
 * 
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UnitsHelper
{
    /**
     * Retrieves a list of language-dependent measurement units
     * according to the current UI locale as an associative array
     * with short name => long name value pairs (e.g. CM => Centimeters).
     *
     * @return array
     */
    public static function getUnits()
    {
        return array(
            t('M') => t('Meters'),
            t('MM') => t('Millimeters'),
            t('CM') => t('Centimeters'),
            t('KM') => t('Kilometers'),
            t('IN') => t('Inch'),
            t('B') => t('Bytes'),
            t('KB') => t('Kilobytes'),
            t('MB') => t('Megabytes'),
            t('GB') => t('Gigabytes'),
            t('TB') => t('Terabytes'),
            t('PX') => t('Pixels'),
            t('G') => t('Grams'),
            t('MG') => t('Milligrams'),
            t('CG') => t('Centigrams'),
            t('KG') => t('Kilograms'),
            t('M3') => t('Volume'),
            '°C' => t('Degrees Centigrade'),
            '°F' => t('Degrees Fahrenheit'),
            t('W') => t('Watts'),
            t('MW') => t('Milliwatts'),
            t('KW') => t('Kilowatts'),
            t('Hz') => t('Hertz'),
            t('KHz') => t('Kilohertz'),
            t('MHz') => t('Megahertz'),
            t('GHz') => t('Gigahertz'),
        );
    }
}