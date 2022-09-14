<?php
/**
 * File containing the {@see AppUtils\ConvertHelper} class.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper
 */

namespace AppUtils;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\ConvertHelper\WordSplitter;
use DateInterval;
use DateTime;
use JsonException;

/**
 * Static conversion helper class: offers a number of utility methods
 * to convert variable types, as well as specialized methods for working
 * with specific types like dates.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper
{
    public const ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER = 23303;
    public const ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE = 23304;
    public const ERROR_JSON_ENCODE_FAILED = 23305;
    public const ERROR_JSON_DECODE_FAILED = 23307;
    public const ERROR_JSON_UNEXPECTED_DECODED_TYPE = 23308;
    public const ERROR_INVALID_BOOLEAN_STRING = 23306;

    public const INTERVAL_DAYS = 'days';
    public const INTERVAL_HOURS = 'hours';
    public const INTERVAL_MINUTES = 'minutes';
    public const INTERVAL_SECONDS = 'seconds';

    /**
     * Normalizes tabs in the specified string by indenting everything
     * back to the minimum tab distance. With the second parameter,
     * tabs can optionally be converted to spaces as well (recommended
     * for HTML output).
     *
     * @param string $string
     * @param boolean $tabs2spaces
     * @return string
     */
    public static function normalizeTabs(string $string, bool $tabs2spaces = false) : string
    {
        return ConvertHelper_String::normalizeTabs($string, $tabs2spaces);
    }

    /**
     * Converts tabs to spaces in the specified string.
     * 
     * @param string $string
     * @param int $tabSize The amount of spaces per tab.
     * @return string
     */
    public static function tabs2spaces(string $string, int $tabSize=4) : string
    {
        return ConvertHelper_String::tabs2spaces($string, $tabSize);
    }
    
   /**
    * Converts spaces to tabs in the specified string.
    * 
    * @param string $string
    * @param int $tabSize The amount of spaces per tab in the source string.
    * @return string
    */
    public static function spaces2tabs(string $string, int $tabSize=4) : string
    {
        return ConvertHelper_String::spaces2tabs($string, $tabSize);
    }

    /**
     * Makes all hidden characters visible in the target string,
     * from spaces to control characters.
     *
     * @param string $string
     * @return string
     */
    public static function hidden2visible(string $string) : string
    {
        return ConvertHelper_String::hidden2visible($string);
    }
    
   /**
    * Converts the specified amount of seconds into
    * a human-readable string split in months, weeks,
    * days, hours, minutes and seconds.
    *
    * @param float $seconds
    * @return string
    */
    public static function time2string($seconds) : string
    {
        $converter = new ConvertHelper_TimeConverter($seconds);
        return $converter->toString();
    }

    /**
     * Converts a timestamp into an easily understandable
     * format, e.g. "2 hours", "1 day", "3 months"
     *
     * If you set the date to parameter, the difference
     * will be calculated between the two dates and not
     * the current time.
     *
     * @param integer|DateTime $datefrom
     * @param integer|DateTime $dateto
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper_DurationConverter::ERROR_NO_DATE_FROM_SET
     */
    public static function duration2string($datefrom, $dateto = -1) : string
    {
         return ConvertHelper_DurationConverter::toString($datefrom, $dateto);
    }

   /**
    * Adds HTML syntax highlighting to the specified SQL string.
    * 
    * @param string $sql
    * @return string
    * @deprecated Use the Highlighter class directly instead.
    * @see Highlighter::sql()
    */
    public static function highlight_sql(string $sql) : string
    {
        return Highlighter::sql($sql);
    }

   /**
    * Adds HTML syntax highlighting to the specified XML code.
    * 
    * @param string $xml The XML to highlight.
    * @param bool $formatSource Whether to format the source with indentation to make it readable.
    * @return string
    * @deprecated Use the Highlighter class directly instead.
    * @see Highlighter::xml()
    */
    public static function highlight_xml(string $xml, bool $formatSource=false) : string
    {
        return Highlighter::xml($xml, $formatSource);
    }

   /**
    * @param string $phpCode
    * @return string
    * @deprecated Use the Highlighter class directly instead.
    * @see Highlighter::php()
    */
    public static function highlight_php(string $phpCode) : string
    {
        return Highlighter::php($phpCode);
    }
    
   /**
    * Converts a number of bytes to a human-readable form,
    * e.g. xx Kb / xx Mb / xx Gb
    *
    * @param int $bytes The amount of bytes to convert.
    * @param int $precision The amount of decimals
    * @param int $base The base to calculate with: Base 10 is default (=1000 Bytes in a KB), Base 2 is mainly used for Windows memory (=1024 Bytes in a KB).
    * @return string
    * 
    * @see https://en.m.wikipedia.org/wiki/Megabyte#Definitions
    */
    public static function bytes2readable(int $bytes, int $precision = 1, int $base = ConvertHelper_StorageSizeEnum::BASE_10) : string
    {
        return self::parseBytes($bytes)->toString($precision, $base);
    }
    
   /**
    * Parses a number of bytes, and creates a converter instance which
    * allows doing common operations with it.
    * 
    * @param int $bytes
    * @return ConvertHelper_ByteConverter
    */
    public static function parseBytes(int $bytes) : ConvertHelper_ByteConverter
    {
        return new ConvertHelper_ByteConverter($bytes);
    }

   /**
    * Cuts a text to the specified length if it is longer than the
    * target length. Appends a text to signify it has been cut at 
    * the end of the string.
    * 
    * @param string $text
    * @param int $targetLength
    * @param string $append
    * @return string
    */
    public static function text_cut(string $text, int $targetLength, string $append = '...') : string
    {
        return ConvertHelper_String::cutText($text, $targetLength, $append);
    }

    /**
     * Pretty `var_dump`.
     *
     * @param mixed $var
     * @param bool $html
     * @return string
     */
    public static function var_dump($var, bool $html=true) : string
    {
        $info = parseVariable($var);
        
        if($html) {
            return $info->toHTML();
        }
        
        return $info->toString();
    }
    
   /**
    * Pretty `print_r`.
    * 
    * @param mixed $var The variable to dump.
    * @param bool $return Whether to return the dumped code.
    * @param bool $html Whether to style the dump as HTML.
    * @return string
    */
    public static function print_r($var, bool $return=false, bool $html=true) : string
    {
        $result = parseVariable($var)->enableType()->toString();
        
        if($html) 
        {
            $result = 
            '<pre style="background:#fff;color:#333;padding:16px;border:solid 1px #bbb;border-radius:4px">'.
                $result.
            '</pre>';
        }
        
        if(!$return) 
        {
            echo $result;
        }
        
        return $result;
    }
    
   /**
    * Converts a string, number or boolean value to a boolean value.
    *
    * @param mixed $string
    * @throws ConvertHelper_Exception
    * @return bool
    *
    * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
    */
    public static function string2bool($string) : bool
    {
        return ConvertHelper_Bool::fromString($string);
    }

   /**
    * Whether the specified string is a boolean string or boolean value.
    * Alias for {@link ConvertHelper::isBoolean()}.
    *
    * @param mixed $string
    * @return bool
    * @deprecated
    * @see ConvertHelper::isBoolean()
    */
    public static function isBooleanString($string) : bool
    {
        return self::isBoolean($string);
    }

    /**
     * Alias for the {@\AppUtils\XMLHelper::string2xml()} method.
     *
     * @param string $text
     * @return string
     * @throws XMLHelper_Exception
     * @deprecated Use the XMLHelper method instead.
     */
    public static function text_makeXMLCompliant(string $text) : string
    {
        return XMLHelper::string2xml($text);
    }

    /**
     * Transforms a date into a generic human-readable date, optionally with time.
     * If the year is the same as the current one, it is omitted.
     *
     * - 6 Jan 2012
     * - 12 Dec 2012 17:45
     * - 5 Aug
     *
     * @param DateTime $date
     * @param bool $includeTime
     * @param bool $shortMonth
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
     */
    public static function date2listLabel(DateTime $date, bool $includeTime = false, bool $shortMonth = false) : string
    {
        return ConvertHelper_Date::toListLabel($date, $includeTime, $shortMonth);
    }

    /**
     * Returns a human-readable month name given the month number. Can optionally
     * return the shorthand version of the month. Translated into the current
     * application locale.
     *
     * @param int|string $monthNr
     * @param boolean $short
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
     */
    public static function month2string($monthNr, bool $short = false) : string
    {
        return ConvertHelper_Date::month2string($monthNr, $short);
    }

    /**
     * Transliterates a string.
     *
     * @param string $string
     * @param string $spaceChar
     * @param bool $lowercase
     * @return string
     */
    public static function transliterate(string $string, string $spaceChar = '-', bool $lowercase = true) : string
    {
        return ConvertHelper_String::transliterate($string, $spaceChar, $lowercase);
    }
    
   /**
    * Retrieves the HEX character codes for all control
    * characters that the {@link stripControlCharacters()} 
    * method will remove.
    * 
    * @return string[]
    */
    public static function getControlCharactersAsHex() : array
    {
        return self::createControlCharacters()->getCharsAsHex();
    }
    
   /**
    * Retrieves an array of all control characters that
    * the {@link stripControlCharacters()} method will 
    * remove, as the actual UTF-8 characters.
    * 
    * @return string[]
    */
    public static function getControlCharactersAsUTF8() : array
    {
        return self::createControlCharacters()->getCharsAsUTF8();
    }
    
   /**
    * Retrieves all control characters as JSON encoded
    * characters, e.g. "\u200b".
    * 
    * @return string[]
    */
    public static function getControlCharactersAsJSON() : array
    {
        return self::createControlCharacters()->getCharsAsJSON();
    }

    /**
     * Removes all control characters from the specified string
     * that can cause problems in some cases, like creating
     * valid XML documents. This includes invisible non-breaking
     * spaces.
     *
     * @param string $string
     * @return string
     * @throws ConvertHelper_Exception
     */
    public static function stripControlCharacters(string $string) : string
    {
        return self::createControlCharacters()->stripControlCharacters($string);
    }
    
   /**
    * Creates the control characters class, used to 
    * work with control characters in strings.
    * 
    * @return ConvertHelper_ControlCharacters
    */
    public static function createControlCharacters() : ConvertHelper_ControlCharacters
    {
        return new ConvertHelper_ControlCharacters();
    }

   /**
    * Converts a unicode character to the PHP notation.
    * 
    * Example:
    * 
    * <pre>unicodeChar2php('"\u0000"')</pre>
    * 
    * Returns
    * 
    * <pre>\x0</pre>
    * 
    * @param string $unicodeChar
    * @return string
    */
    public static function unicodeChar2php(string $unicodeChar) : string 
    {
        $unicodeChar = json_decode($unicodeChar);
        
        $output = '';
        $split = str_split($unicodeChar);
        
        foreach($split as $octet) 
        {
            $ordInt = ord($octet);
            // Convert from int (base 10) to hex (base 16), for PHP \x syntax
            $ordHex = base_convert((string)$ordInt, 10, 16);
            $output .= '\x' . $ordHex;
        }
        
        return $output;
    }
    
    /**
     * Removes the extension from the specified filename
     * and returns the name without the extension.
     *
     * Examples:
     *
     * <ul>
     *   <li>filename.html > filename</li>
     *   <li>passed.test.jpg > passed.test</li>
     *   <li>path/to/file/document.txt > document</li>
     * </ul>
     *
     * @param string $filename
     * @return string
     */
    public static function filenameRemoveExtension(string $filename) : string
    {
        return FileHelper::removeExtension($filename);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
     */
    public static function areVariablesEqual($a, $b) : bool
    {
        return ConvertHelper_Comparator::areVariablesEqual($a, $b);
    }

    /**
     * Compares two strings to check whether they are equal.
     * null and empty strings are considered equal.
     *
     * @param string|NULL $a
     * @param string|NULL $b
     * @return boolean
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
     */
    public static function areStringsEqual(?string $a, ?string $b) : bool
    {
        return ConvertHelper_Comparator::areStringsEqual($a, $b);
    }

    /**
     * Checks whether the two specified numbers are equal.
     * null and empty strings are considered as 0 values.
     *
     * @param number|string|NULL $a
     * @param number|string|NULL $b
     * @return boolean
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
     */
    public static function areNumbersEqual($a, $b) : bool
    {
        return ConvertHelper_Comparator::areNumbersEqual($a, $b);
    }

    /**
     * Converts a boolean value to a string. Defaults to returning
     * 'true' or 'false', with the additional parameter it can also
     * return the 'yes' and 'no' variants.
     *
     * @param boolean|string|int $boolean
     * @param boolean $yesNo
     * @return string
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
     */
    public static function bool2string($boolean, bool $yesNo = false) : string
    {
        return ConvertHelper_Bool::toString($boolean, $yesNo);
    }

    /**
     * Converts a strict boolean value to string.
     * Cannot throw an exception like {@see ConvertHelper::bool2string()}
     * does.
     *
     * @param bool $boolean
     * @param bool $yesNo
     * @return string
     */
    public static function boolStrict2string(bool $boolean, bool $yesNo = false) : string
    {
        return ConvertHelper_Bool::toStringStrict($boolean, $yesNo);
    }

   /**
    * Converts an associative array with attribute name > value pairs
    * to an attribute string that can be used in an HTML tag. Empty 
    * attribute values are ignored.
    * 
    * Example:
    * 
    * array2attributeString(array(
    *     'id' => 45,
    *     'href' => 'http://www.mistralys.com'
    * ));
    * 
    * Result:
    * 
    * id="45" href="http://www.mistralys.com"
    * 
    * @param array<string,mixed> $array
    * @return string
    */
    public static function array2attributeString(array $array) : string
    {
        return ConvertHelper_Array::toAttributeString($array);
    }

    /**
     * Converts an array to a JSON string. Alias for
     * the method {@see ConvertHelper::var2json()}.
     *
     * @param array<mixed> $array
     * @return string
     *
     * @throws ConvertHelper_Exception
     */
    public static function array2json(array $array) : string
    {
        return self::var2json($array);
    }
    
   /**
    * Converts a string, so it can safely be used in a javascript
    * statement in an HTML tag: uses single quotes around the string
    * and encodes all special characters as needed.
    * 
    * @param string $string
    * @return string
    * @deprecated Use the JSHelper class instead.
    * @see JSHelper::phpVariable2AttributeJS()
    */
    public static function string2attributeJS(string $string) : string
    {
        return JSHelper::phpVariable2AttributeJS($string);
    }
    
   /**
    * Checks if the specified string is a boolean value, which
    * includes string representations of boolean values, like 
    * <code>yes</code> or <code>no</code>, and <code>true</code>
    * or <code>false</code>.
    * 
    * @param mixed $value
    * @return boolean
    */
    public static function isBoolean($value) : bool
    {
        return ConvertHelper_Bool::isBoolean($value);
    }
    
   /**
    * Converts an associative array to an HTML style attribute value string.
    * 
    * @param array<string,mixed> $subject
    * @return string
    */
    public static function array2styleString(array $subject) : string
    {
        return ConvertHelper_Array::toStyleString($subject);
    }
    
   /**
    * Converts a DateTime object to a timestamp, which
    * is PHP 5.2 compatible.
    * 
    * @param DateTime $date
    * @return integer
    */
    public static function date2timestamp(DateTime $date) : int
    {
        return ConvertHelper_Date::toTimestamp($date);
    }
    
   /**
    * Converts a timestamp into a DateTime instance.
    * @param int $timestamp
    * @return DateTime
    */
    public static function timestamp2date(int $timestamp) : DateTime
    {
        return ConvertHelper_Date::fromTimestamp($timestamp);
    }
    
   /**
    * Strips an absolute path to a file within the application
    * to make the path relative to the application root path.
    * 
    * @param string $path
    * @return string
    * 
    * @see FileHelper::relativizePath()
    * @see FileHelper::relativizePathByDepth()
    */
    public static function fileRelativize(string $path) : string
    {
        return FileHelper::relativizePathByDepth($path);
    }
    
    /**
    * Converts a PHP regex to a javascript RegExp object statement.
    * 
    * NOTE: This is an alias for the JSHelper's `convertRegex` method. 
    * More details are available on its usage there.
    *
    * @param string $regex A PHP preg regex
    * @param string $statementType The type of statement to return: Defaults to a statement to create a RegExp object.
    * @return string Depending on the specified return type.
    * 
    * @see JSHelper::buildRegexStatement()
    */
    public static function regex2js(string $regex, string $statementType=JSHelper::JS_REGEX_OBJECT) : string
    {
        return JSHelper::buildRegexStatement($regex, $statementType);
    }
    
   /**
    * Converts the specified variable to a JSON string.
    *
    * Works just like the native `json_encode` method,
    * except that it will trigger an exception on failure,
    * which has the json error details included in its
    * developer details.
    * 
    * @param mixed $variable
    * @param int $options JSON encode options.
    * @param int $depth 
    * @return string
    *
    * @throws JSONConverterException
    * @see ConvertHelper::ERROR_JSON_ENCODE_FAILED
    */
    public static function var2json($variable, int $options=0, int $depth=512) : string
    {
        return JSONConverter::var2json($variable, $options, $depth);
    }

    /**
     * Decodes a JSON encoded string to the relevant variable type.
     *
     * @param string $json
     * @return mixed
     * @throws JSONConverterException
     */
    public static function json2var(string $json)
    {
        return JSONConverter::json2var($json);
    }

    /**
     * Attempts to convert a JSON string explicitly to
     * an associative array.
     *
     * @param array<mixed>|string $json Either a JSON-encoded string or an array,
     *                                  which will be passed through as-is, to
     *                                  avoid having to check if the string has
     *                                  already been decoded.
     * @return array<mixed>
     * @throws JSONConverterException
     */
    public static function json2array($json) : array
    {
        return JSONConverter::json2array($json);
    }

    /**
     * Converts any PHP variable to a human-readable
     * string representation, like "object ClassName"
     *
     * @param mixed $variable
     * @return string
     */
    public function var2string($variable) : string
    {
        return parseVariable($variable)
            ->enableType()
            ->toString();
    }
    
   /**
    * Strips all known UTF byte order marks from the specified string.
    * 
    * @param string $string
    * @return string
    */
    public static function stripUTFBom(string $string) : string
    {
        $boms = FileHelper::createUnicodeHandling()->getUTFBOMs();

        foreach($boms as $bomChars)
        {
            $length = mb_strlen($bomChars);
            $text = mb_substr($string, 0, $length);

            if($text===$bomChars)
            {
                return mb_substr($string, $length);
            }
        }
        
        return $string;
    }

   /**
    * Converts a string to valid utf8, regardless
    * of the string's encoding(s).
    * 
    * @param string $string
    * @return string
    */
    public static function string2utf8(string $string) : string
    {
        return ConvertHelper_String::toUtf8($string);
    }
    
   /**
    * Checks whether the specified string is an ASCII
    * string, without any special or UTF8 characters.
    * Note: empty strings and NULL are considered ASCII.
    * Any variable types other than strings are not.
    * 
    * @param string|float|int|NULL $string
    * @return boolean
    */
    public static function isStringASCII($string) : bool
    {
        return ConvertHelper_String::isASCII(strval($string));
    }
    
   /**
    * Adds HTML syntax highlighting to an URL.
    * 
    * NOTE: Includes the necessary CSS styles. When
    * highlighting several URLs in the same page,
    * prefer using the `parseURL` function instead.
    * 
    * @param string $url
    * @return string
    * @deprecated Use the Highlighter class directly instead.
    * @see Highlighter
    */
    public static function highlight_url(string $url) : string
    {
        return Highlighter::url($url);
    }

   /**
    * Calculates a percentage match of the source string with the target string.
    * 
    * Options are:
    * 
    * - maxLevenshtein, default: 10
    *   Any levenshtein results above this value are ignored.
    *   
    * - precision, default: 1
    *   The precision of the percentage float value
    * 
    * @param string $source
    * @param string $target
    * @param array<string,mixed> $options
    * @return float
    *
    * @see ConvertHelper_TextComparer
    * @see ConvertHelper_TextComparer::OPTION_MAX_LEVENSHTEIN_DISTANCE
    * @see ConvertHelper_TextComparer::OPTION_PRECISION
    */
    public static function matchString(string $source, string $target, array $options=array()) : float
    {
        return (new ConvertHelper_TextComparer())
            ->setOptions($options)
            ->match($source, $target);
    }
    
   /**
    * Converts a date interval to a human-readable string with
    * all necessary time components, e.g. "1 year, 2 months and 4 days".
    * 
    * @param DateInterval $interval
    * @return string
    * @see ConvertHelper_IntervalConverter
    *
    * @throws ConvertHelper_Exception
    * @see ConvertHelper_IntervalConverter::ERROR_MISSING_TRANSLATION
    */
    public static function interval2string(DateInterval $interval) : string
    {
        return (new ConvertHelper_IntervalConverter())
            ->toString($interval);
    }
    
   /**
    * Converts an interval to its total amount of days.
    * @param DateInterval $interval
    * @return int
    */
    public static function interval2days(DateInterval $interval) : int
    {
        return ConvertHelper_DateInterval::toDays($interval);
    }

   /**
    * Converts an interval to its total amount of hours.
    * @param DateInterval $interval
    * @return int
    */
    public static function interval2hours(DateInterval $interval) : int
    {
        return ConvertHelper_DateInterval::toHours($interval);
    }
    
   /**
    * Converts an interval to its total amount of minutes. 
    * @param DateInterval $interval
    * @return int
    */
    public static function interval2minutes(DateInterval $interval) : int
    {
        return ConvertHelper_DateInterval::toMinutes($interval);
    }
    
   /**
    * Converts an interval to its total amount of seconds.
    * @param DateInterval $interval
    * @return int
    */    
    public static function interval2seconds(DateInterval $interval) : int
    {
        return ConvertHelper_DateInterval::toSeconds($interval);
    }
    
   /**
    * Calculates the total amount of days / hours / minutes or seconds
    * of a date interval object (depending on the specified units), and
    * returns the total amount.
    * 
    * @param DateInterval $interval
    * @param string $unit What total value to calculate.
    * @return integer
    * 
    * @see ConvertHelper::INTERVAL_SECONDS
    * @see ConvertHelper::INTERVAL_MINUTES
    * @see ConvertHelper::INTERVAL_HOURS
    * @see ConvertHelper::INTERVAL_DAYS
    */
    public static function interval2total(DateInterval $interval, string $unit=self::INTERVAL_SECONDS) : int
    {
        return ConvertHelper_DateInterval::toTotal($interval, $unit);
    }

   /**
    * Converts a date to the corresponding day name.
    * 
    * @param DateTime $date
    * @param bool $short
    * @return string|NULL
    */
    public static function date2dayName(DateTime $date, bool $short=false) : ?string
    {
        return ConvertHelper_Date::toDayName($date, $short);
    }
    
   /**
    * Retrieves a list of english day names.
    * @return string[]
    */
    public static function getDayNamesInvariant() : array
    {
        return ConvertHelper_Date::getDayNamesInvariant();
    }
    
   /**
    * Retrieves the day names list for the current locale.
    * 
    * @param bool $short
    * @return string[]
    */
    public static function getDayNames(bool $short=false) : array
    {
        return ConvertHelper_Date::getDayNames($short);
    }

    /**
     * Implodes an array with a separator character, and the last item with "add".
     * 
     * @param string[] $list The indexed array with items to implode.
     * @param string $sep The separator character to use.
     * @param string $conjunction The word to use as conjunction with the last item in the list. NOTE: include spaces as needed.
     * @return string
     */
    public static function implodeWithAnd(array $list, string $sep = ', ', string $conjunction = '') : string
    {
        return ConvertHelper_Array::implodeWithAnd($list, $sep, $conjunction);
    }
    
   /**
    * Splits a string into an array of all characters it is composed of.
    * Unicode character safe.
    * 
    * NOTE: Spaces and newlines (both \r and \n) are also considered single
    * characters.
    * 
    * @param string $string
    * @return string[]
    */
    public static function string2array(string $string) : array
    {
        return ConvertHelper_String::toArray($string);
    }

    public static function string2words(string $string) : WordSplitter
    {
        return new WordSplitter($string);
    }
    
   /**
    * Checks whether the specified string contains HTML code.
    * 
    * @param string $string
    * @return boolean
    */
    public static function isStringHTML(string $string) : bool
    {
        return ConvertHelper_String::isHTML($string);
    }
    
   /**
    * UTF8-safe wordwrap method: works like the regular wordwrap
    * PHP function but compatible with UTF8. Otherwise the lengths
    * are not calculated correctly.
    * 
    * @param string $str
    * @param int $width
    * @param string $break
    * @param bool $cut
    * @return string
    */
    public static function wordwrap(string $str, int $width = 75, string $break = "\n", bool $cut = false) : string 
    {
        return ConvertHelper_String::wordwrap($str, $width, $break, $cut);
    }
    
   /**
    * Calculates the byte length of a string, taking into 
    * account any unicode characters.
    * 
    * @param string $string
    * @return int
    */
    public static function string2bytes(string $string): int
    {
        return ConvertHelper_String::toBytes($string);
    }
    
   /**
    * Creates a short, 8-character long hash for the specified string.
    * 
    * WARNING: Not cryptographically safe.
    * 
    * @param string $string
    * @return string
    */
    public static function string2shortHash(string $string) : string
    {
        return ConvertHelper_String::toShortHash($string);
    }

    /**
     * Converts a string into an MD5 hash.
     *
     * @param string $string
     * @return string
     */
    public static function string2hash(string $string): string
    {
        return ConvertHelper_String::toHash($string);
    }

    /**
     * Converts the specified callable to string.
     *
     * NOTE: Will work even if the callable is not
     * actually callable, as compared to
     * `parseVariable($callback)->toString()`.
     *
     * @param callable|array{0:string,1:string} $callback
     * @return string
     */
    public static function callback2string($callback) : string
    {
        // We are creating the renderer manually, to allow rendering
        // callbacks to string even if they are not actually callable.
        $renderer = new VariableInfo_Renderer_String_Callable(parseVariable($callback));

        return $renderer->render();
    }

    public static function exception2info(\Throwable $e) : ConvertHelper_ThrowableInfo
    {
        return self::throwable2info($e);
    }
    
    public static function throwable2info(\Throwable $e) : ConvertHelper_ThrowableInfo
    {
        return ConvertHelper_ThrowableInfo::fromThrowable($e);
    }
    
   /**
    * Parses the specified query string like the native 
    * function <code>parse_str</code>, without the key
    * naming limitations.
    * 
    * Using parse_str, dots or spaces in key names are 
    * replaced by underscores. This method keeps all names
    * intact.
    * 
    * It still uses the parse_str implementation as it 
    * is tested and tried, but fixes the parameter names
    * after parsing, as needed.
    * 
    * @param string $queryString
    * @return array<string,string>
    * @see ConvertHelper_QueryParser
    */
    public static function parseQueryString(string $queryString) : array
    {
        $parser = new ConvertHelper_QueryParser();
        return $parser->parse($queryString);
    }

   /**
    * Searches for needle in the specified string, and returns a list
    * of all occurrences, including the matched string. The matched 
    * string is useful when doing a case-insensitive search, as it
    * shows the exact matched case of needle.
    *   
    * @param string $needle
    * @param string $haystack
    * @param bool $caseInsensitive
    * @return ConvertHelper_StringMatch[]
    */
    public static function findString(string $needle, string $haystack, bool $caseInsensitive=false): array
    {
        return ConvertHelper_String::findString($needle, $haystack, $caseInsensitive);
    }
    
   /**
    * Like explode, but trims all entries, and removes 
    * empty entries from the resulting array.
    * 
    * @param string $delimiter
    * @param string $string
    * @return string[]
    */
    public static function explodeTrim(string $delimiter, string $string) : array
    {
        return ConvertHelper_String::explodeTrim($delimiter, $string);
    }
    
   /**
    * Detects the most used end-of-line character in the subject string.
    * 
    * @param string $subjectString The string to check.
    * @return NULL|ConvertHelper_EOL The detected EOL instance, or NULL if none has been detected.
    */
    public static function detectEOLCharacter(string $subjectString) : ?ConvertHelper_EOL
    {
        return ConvertHelper_EOL::detect($subjectString);
    }

   /**
    * Removes the specified keys from the target array,
    * if they exist.
    * 
    * @param array<string|int,mixed> $array
    * @param string[] $keys
    */
    public static function arrayRemoveKeys(array &$array, array $keys) : void
    {
        ConvertHelper_Array::removeKeys($array, $keys);
    }
    
   /**
    * Checks if the specified variable is an integer or a string containing an integer.
    * Accepts both positive and negative integers.
    * 
    * @param mixed $value
    * @return bool
    */
    public static function isInteger($value) : bool
    {
        if(is_int($value)) {
            return true;
        }
        
        // booleans get converted to numbers, so they would
        // actually match the regex.
        if(is_bool($value)) {
            return false;
        }
        
        if(is_string($value) && $value !== '') {
            return preg_match('/\A-?\d+\z/', $value) === 1;
        }
        
        return false;    
    }
    
   /**
    * Converts an amount of seconds to a DateInterval object.
    * 
    * @param int $seconds
    * @return DateInterval
    * @throws ConvertHelper_Exception If the date interval cannot be created.
    * 
    * @see ConvertHelper::ERROR_CANNOT_GET_DATE_DIFF
    */
    public static function seconds2interval(int $seconds) : DateInterval
    {
        return ConvertHelper_DateInterval::fromSeconds($seconds)->getInterval();
    }
    
   /**
    * Converts a size string like "50 MB" to the corresponding byte size.
    * It is case-insensitive, ignores spaces, and supports both traditional
    * "MB" and "MiB" notations.
    * 
    * @param string $size
    * @return int
    */
    public static function size2bytes(string $size) : int
    {
        return self::parseSize($size)->toBytes();
    }
    
   /**
    * Parses a size string like "50 MB" and returns a size notation instance
    * that has utility methods to access information on it, and convert it.
    * 
    * @param string $size
    * @return ConvertHelper_SizeNotation
    */
    public static function parseSize(string $size) : ConvertHelper_SizeNotation
    {
        return new ConvertHelper_SizeNotation($size);
    }
    
   /**
    * Creates a URL finder instance, which can be used to find
    * URLs in a string - be it plain text, or HTML.
    * 
    * @param string $subject
    * @return ConvertHelper_URLFinder
    */
    public static function createURLFinder(string $subject) : ConvertHelper_URLFinder
    {
        return new ConvertHelper_URLFinder($subject);
    }

    /**
     * Removes the target values from the source array.
     *
     * @param array<number|string,mixed> $sourceArray
     * @param array<number|string,mixed> $values
     * @param bool $keepKeys
     * @return array<number|string,mixed>
     */
    public static function arrayRemoveValues(array $sourceArray, array $values, bool $keepKeys=false) : array
    {
        return ConvertHelper_Array::removeValues($sourceArray, $values, $keepKeys);
    }
}
