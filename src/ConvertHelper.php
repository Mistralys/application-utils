<?php
/**
 * File containing the {@see AppUtils\ConvertHelper} class.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper
 */

namespace AppUtils;

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
    const ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER = 23303;
    
    const ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE = 23304;
    
    const ERROR_JSON_ENCODE_FAILED = 23305;
    
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
    public static function normalizeTabs(string $string, bool $tabs2spaces = false)
    {
        $lines = explode("\n", $string);
        $max = 0;
        $min = 99999;
        foreach ($lines as $line) {
            $amount = substr_count($line, "\t");
            if ($amount > $max) {
                $max = $amount;
            }

            if ($amount > 0 && $amount < $min) {
                $min = $amount;
            }
        }

        $converted = array();
        foreach ($lines as $line) {
            $amount = substr_count($line, "\t") - $min;
            $line = trim($line);
            if ($amount >= 1) {
                $line = str_repeat("\t", $amount) . $line;
            }

            $converted[] = $line;
        }

        $string = implode("\n", $converted);
        if ($tabs2spaces) {
            $string = self::tabs2spaces($string);
        }

        return $string;
    }

    /**
     * Converts tabs to spaces in the specified string.
     * @param string $string
     * @return string
     */
    public static function tabs2spaces($string)
    {
        return str_replace("\t", '    ', $string);
    }
    
    /**
     * Converts the specified amount of seconds into
     * a human readable string split in months, weeks,
     * days, hours, minutes and seconds.
     *
     * @param float $seconds
     * @return string
     */
    public static function time2string($seconds)
    {
        static $units = null;
        if (is_null($units)) {
            $units = array(
                array(
                    'value' => 31 * 7 * 24 * 3600,
                    'singular' => t('month'),
                    'plural' => t('months')
                ),
                array(
                    'value' => 7 * 24 * 3600,
                    'singular' => t('week'),
                    'plural' => t('weeks')
                ),
                array(
                    'value' => 24 * 3600,
                    'singular' => t('day'),
                    'plural' => t('days')
                ),
                array(
                    'value' => 3600,
                    'singular' => t('hour'),
                    'plural' => t('hours')
                ),
                array(
                    'value' => 60,
                    'singular' => t('minute'),
                    'plural' => t('minutes')
                ),
                array(
                    'value' => 1,
                    'singular' => t('second'),
                    'plural' => t('seconds')
                )
            );
        }

        // specifically handle zero
        if ($seconds <= 0) {
            return '0 ' . t('seconds');
        }
        
        if($seconds < 1) {
            return t('less than a second');
        }

        $tokens = array();
        foreach ($units as $def) {
            $quot = intval($seconds / $def['value']);
            if ($quot) {
                $item = $quot . ' ';
                if (abs($quot) > 1) {
                    $item .= $def['plural'];
                } else {
                    $item .= $def['singular'];
                }

                $tokens[] = $item;
                $seconds -= $quot * $def['value'];
            }
        }

        $last = array_pop($tokens);
        if (empty($tokens)) {
            return $last;
        }

        return implode(', ', $tokens) . ' ' . t('and') . ' ' . $last;
    }

   /**
    * Converts a timestamp into an easily understandable
    * format, e.g. "2 hours", "1 day", "3 months"
    *
    * If you set the date to parameter, the difference
    * will be calculated between the two dates and not
    * the current time.
    *
    * @param integer|\DateTime $datefrom
    * @param integer|\DateTime $dateto
    * @return string
    */
    public static function duration2string($datefrom, $dateto = -1) : string
    {
         $converter = new ConvertHelper_DurationConverter();
         
         if($datefrom instanceof \DateTime)
         {
             $converter->setDateFrom($datefrom);
         }
         else
         {
             $converter->setDateFrom(self::timestamp2date($datefrom)); 
         }

         if($dateto instanceof \DateTime)
         {
             $converter->setDateTo($dateto);
         }
         else if($dateto > 0)
         {
             $converter->setDateTo(self::timestamp2date($dateto));
         }

         return $converter->convert();
    }

    /**
     * Adds syntax highlighting to the specified SQL string in HTML format
     * @param string $sql
     * @return string
     */
    public static function highlight_sql($sql)
    {
        $geshi = new  \GeSHi($sql, 'sql');

        return $geshi->parse_code();
    }
    
    public static function highlight_xml($xml, $formatSource=false)
    {
        if($formatSource) 
        {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            $xml = $dom->saveXML();
        }
        
        $geshi = new \GeSHi($xml, 'xml');
        
        return $geshi->parse_code();
    }

    public static function highlight_php($php)
    {
        $geshi = new \GeSHi($php, 'php');
    
        return $geshi->parse_code();
    }
    
   /**
    * Converts a number of bytes to a human readable form,
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
        $length = mb_strlen($text);
        if ($length <= $targetLength) {
            return $text;
        }

        $text = trim(mb_substr($text, 0, $targetLength)) . $append;

        return $text;
    }

    public static function var_dump($var, $html=true)
    {
        $info = parseVariable($var);
        
        if($html) {
            return $info->toHTML();
        }
        
        return $info->toString();
    }
    
    public static function print_r($var, $return=false, $html=true)
    {
        $result = self::var_dump($var, $html);
        
        if($html) {
            $result = 
            '<pre style="background:#fff;color:#333;padding:16px;border:solid 1px #bbb;border-radius:4px">'.
                $result.
            '</pre>';
        }
        
        if($return) {
            return $result;
        }
        
        echo $result;
    }
    
    protected static $booleanStrings = array(
        1 => true,
        0 => false,
        '1' => true,
        '0' => false,
        'true' => true,
        'false' => false,
        'yes' => true,
        'no' => false
    );

    public static function string2bool($string)
    {
        if($string === '' || $string === null) {
            return false;
        }
        
        if (is_bool($string)) {
            return $string;
        }

        if (!array_key_exists($string, self::$booleanStrings)) {
            throw new \InvalidArgumentException('Invalid string boolean representation');
        }

        return self::$booleanStrings[$string];
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
    * @deprecated
    */
    public static function text_makeXMLCompliant($text)
    {
        return XMLHelper::string2xml($text);
    }

    /**
     * Transforms a date into a generic human readable date, optionally with time.
     * If the year is the same as the current one, it is omitted.
     *
     * - 6 Jan 2012
     * - 12 Dec 2012 17:45
     * - 5 Aug
     *
     * @param \DateTime $date
     * @return string
     */
    public static function date2listLabel(\DateTime $date, $includeTime = false, $shortMonth = false)
    {
        $today = new \DateTime();
        if($date->format('d.m.Y') == $today->format('d.m.Y')) {
            $label = t('Today');
        } else {
            $label = $date->format('d') . '. ' . self::month2string((int)$date->format('m'), $shortMonth) . ' ';
            if ($date->format('Y') != date('Y')) {
                $label .= $date->format('Y');
            }
        }
        
        if ($includeTime) {
            $label .= $date->format(' H:i');
        }

        return trim($label);
    }

    protected static $months;

    /**
     * Returns a human readable month name given the month number. Can optionally
     * return the shorthand version of the month. Translated into the current
     * application locale.
     *
     * @param int|string $monthNr
     * @param boolean $short
     * @throws ConvertHelper_Exception
     * @return string
     */
    public static function month2string($monthNr, $short = false)
    {
        if (!isset(self::$months)) {
            self::$months = array(
                1 => array(t('January'), t('Jan')),
                2 => array(t('February'), t('Feb')),
                3 => array(t('March'), t('Mar')),
                4 => array(t('April'), t('Apr')),
                5 => array(t('May'), t('May')),
                6 => array(t('June'), t('Jun')),
                7 => array(t('July'), t('Jul')),
                8 => array(t('August'), t('Aug')),
                9 => array(t('September'), t('Sep')),
                10 => array(t('October'), t('Oct')),
                11 => array(t('November'), t('Nov')),
                12 => array(t('December'), t('Dec'))
            );
        }

        $monthNr = intval($monthNr);
        if (!isset(self::$months[$monthNr])) {
            throw new ConvertHelper_Exception(
                'Invalid month number',
                sprintf('%1$s is not a valid month number.', $monthNr),
                self::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
            );
        }

        if ($short) {
            return self::$months[$monthNr][1];
        }

        return self::$months[$monthNr][0];
    }

    /**
     * Transliterates a string.
     *
     * @param string $string
     * @param string $spaceChar
     * @param string $lowercase
     * @return string
     */
    public static function transliterate($string, $spaceChar = '-', $lowercase = true)
    {
        $translit = new Transliteration();
        $translit->setSpaceReplacement($spaceChar);
        if ($lowercase) {
            $translit->setLowercase();
        }

        return $translit->convert($string);
    }
    
   /**
    * Retrieves the HEX character codes for all control
    * characters that the {@link stripControlCharacters()} 
    * method will remove.
    * 
    * @return string[]
    */
    public static function getControlCharactersAsHex()
    {
        $hexAlphabet = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        
        $stack = array();
        foreach(self::$controlChars as $char)
        {
            $tokens = explode('-', $char);
            $start = $tokens[0];
            $end = $tokens[1];
            $prefix = substr($start, 0, 3);
            $range = array();
            foreach($hexAlphabet as $number) {
                $range[] = $prefix.$number;
            }
            
            $use = false;
            foreach($range as $number) {
                if($number == $start) {
                    $use = true;
                }
                
                if($use) {
                    $stack[] = $number;
                }
                
                if($number == $end) {
                    break;
                }
            }
        }
        
        return $stack;
    }
    
   /**
    * Retrieves an array of all control characters that
    * the {@link stripControlCharacters()} method will 
    * remove, as the actual UTF-8 characters.
    * 
    * @return string[]
    */
    public static function getControlCharactersAsUTF8()
    {
        $chars = self::getControlCharactersAsHex();
        
        $result = array();
        foreach($chars as $char) {
            $result[] = hex2bin($char);
        }
        
        return $result;
    }
    
   /**
    * Retrieves all control characters as JSON encoded
    * characters, e.g. "\u200b".
    * 
    * @return string[]
    */
    public static function getControlCharactersAsJSON()
    {
        $chars = self::getControlCharactersAsHex();
        
        $result = array();
        foreach($chars as $char) {
            $result[] = '\u'.strtolower($char);
        }
        
        return $result;
    }
    
    protected static $controlChars =  array(
        '0000-0008', // control chars
        '000E-000F', // control chars
        '0010-001F', // control chars
        '2000-200F', // non-breaking space and co
    );
    
    protected static $controlCharsRegex;

    /**
     * Removes all control characters from the specified string
     * that can cause problems in some cases, like creating
     * valid XML documents. This includes invisible non-breaking
     * spaces.
     *
     * @param string $string
     * @return string
     * @see https://stackoverflow.com/a/8171868/2298192
     * @see https://unicode-table.com/en
     */
    public static function stripControlCharacters(string $string) : string
    {
        if(empty($string)) {
            return $string;
        }
        
        // create the regex from the unicode characters list
        if(!isset(self::$controlCharsRegex)) 
        {
            $chars = self::getControlCharactersAsHex();

            // we use the notation \x{0000} to specify the unicode character key
            // in the regular expression.
            $stack = array();
            foreach($chars as $char) {
                $stack[] = '\x{'.$char.'}';
            }
            
            self::$controlCharsRegex = '/['.implode('', $stack).']/u';
        }
        
        return preg_replace(self::$controlCharsRegex, '', $string);
    }

   /**
    * Converts a unicode character to the PHPO notation.
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
    public static function unicodeChar2php($unicodeChar) 
    {
        $unicodeChar = json_decode($unicodeChar);
        
        /** @author Krinkle 2018 */
        $output = '';
        foreach (str_split($unicodeChar) as $octet) {
            $ordInt = ord($octet);
            // Convert from int (base 10) to hex (base 16), for PHP \x syntax
            $ordHex = base_convert($ordInt, 10, 16);
            $output .= '\x' . $ordHex;
        }
        return $output;
    }
    
    /**
     * Removes the extension from the specified filename
     * and returns the name without the extension.
     *
     * Example:
     * filename.html > filename
     * passed.test.jpg > passed.test
     * path/to/file/document.txt > document
     *
     * @param string $filename
     * @return string
     */
    public static function filenameRemoveExtension($filename)
    {
        return FileHelper::removeExtension($filename);
    }
    
    public static function areVariablesEqual($a, $b) : bool
    {
        $a = self::convertScalarForComparison($a);
        $b = self::convertScalarForComparison($b);

        return $a === $b;
    }
    
    protected static function convertScalarForComparison($scalar)
    {
        if($scalar === '' || is_null($scalar)) {
            return null;
        }
        
        if(is_bool($scalar)) {
            return self::bool2string($scalar);
        }
        
        if(is_array($scalar)) {
            $scalar = md5(serialize($scalar));
        }
        
        if($scalar !== null && !is_scalar($scalar)) {
            throw new ConvertHelper_Exception(
                'Not a scalar value in comparison',
                null,
                self::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
            );
        }
        
        return strval($scalar);
    }

    /**
     * Compares two strings to check whether they are equal.
     * null and empty strings are considered equal.
     *
     * @param string $a
     * @param string $b
     * @return boolean
     */
    public static function areStringsEqual($a, $b) : bool
    {
        return self::areVariablesEqual($a, $b);
    }

    /**
     * Checks whether the two specified numbers are equal.
     * null and empty strings are considered as 0 values.
     *
     * @param number|string $a
     * @param number|string $b
     * @return boolean
     */
    public static function areNumbersEqual($a, $b) : bool
    {
        return self::areVariablesEqual($a, $b);
    }

    /**
     * Converts a boolean value to a string. Defaults to returning
     * 'true' or 'false', with the additional parameter it can also
     * return the 'yes' and 'no' variants.
     *
     * @param boolean|string $boolean
     * @param boolean $yesno
     * @return string
     */
    public static function bool2string($boolean, bool $yesno = false) : string
    {
        // allow 'yes', 'true', 'no', 'false' string notations as well
        if(!is_bool($boolean)) {
            $boolean = self::string2bool($boolean);
        }
        
        if ($boolean) {
            if ($yesno) {
                return 'yes';
            }

            return 'true';
        }

        if ($yesno) {
            return 'no';
        }

        return 'false';
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
    * @param array $array
    * @return string
    */
    public static function array2attributeString($array)
    {
        $tokens = array();
        foreach($array as $attr => $value) {
            if($value == '' || $value == null) {
                continue;
            }
            
            $tokens[] = $attr.'="'.$value.'"';
        }
        
        if(empty($tokens)) {
            return '';
        }
        
        return ' '.implode(' ', $tokens);
    }
    
   /**
    * Converts a string so it can safely be used in a javascript
    * statement in an HTML tag: uses single quotes around the string
    * and encodes all special characters as needed.
    * 
    * @param string $string
    * @return string
    */
    public static function string2attributeJS($string, $quoted=true)
    {
        $converted = addslashes(htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8'));
        if($quoted) {
            $converted = "'".$converted."'";
        } 
        
        return $converted;
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
        if(is_bool($value)) {
            return true;
        }
        
        if(!is_scalar($value)) {
            return false;
        }
        
        return array_key_exists($value, self::$booleanStrings);
    }
    
   /**
    * Converts an associative array to an HTML style attribute value string.
    * 
    * @param array $subject
    * @return string
    */
    public static function array2styleString(array $subject) : string
    {
        $tokens = array();
        foreach($subject as $name => $value) {
            $tokens[] = $name.':'.$value;
        }
        
        return implode(';', $tokens);
    }
    
   /**
    * Converts a DateTime object to a timestamp, which
    * is PHP 5.2 compatible.
    * 
    * @param \DateTime $date
    * @return integer
    */
    public static function date2timestamp(\DateTime $date) : int
    {
        return (int)$date->format('U');
    }
    
   /**
    * Converts a timestamp into a DateTime instance.
    * @param int $timestamp
    * @return \DateTime
    */
    public static function timestamp2date(int $timestamp) : \DateTime
    {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        return $date;
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
    * @return array|string Depending on the specified return type.
    * 
    * @see JSHelper::buildRegexStatement()
    */
    public static function regex2js(string $regex, string $statementType=JSHelper::JS_REGEX_OBJECT)
    {
        return JSHelper::buildRegexStatement($regex, $statementType);
    }
    
   /**
    * Converts the specified variable to JSON. Works just
    * like the native `json_encode` method, except that it
    * will trigger an exception on failure, which has the 
    * json error details included in its developer details.
    * 
    * @param mixed $variable
    * @param int|NULL $options JSON encode options.
    * @param int|NULL $depth 
    * @throws ConvertHelper_Exception
    * @return string
    */
    public static function var2json($variable, int $options=0, int $depth=512) : string
    {
        $result = json_encode($variable, $options, $depth);
        
        if($result !== false) {
            return $result;
        }
        
        throw new ConvertHelper_Exception(
            'Could not create json array'.json_last_error_msg(),
            sprintf(
                'The call to json_encode failed for the variable [%s]. JSON error details: #%s, %s',
                parseVariable($variable)->toString(),
                json_last_error(),
                json_last_error_msg()
            ),
            self::ERROR_JSON_ENCODE_FAILED
        );
    }
    
   /**
    * Strips all known UTF byte order marks from the specified string.
    * 
    * @param string $string
    * @return string
    */
    public static function stripUTFBom($string)
    {
        $boms = FileHelper::getUTFBOMs();
        foreach($boms as $bomChars) {
            $length = mb_strlen($bomChars);
            $text = mb_substr($string, 0, $length);
            if($text==$bomChars) {
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
    public static function string2utf8($string)
    {
        if(!self::isStringASCII($string)) {
            return \ForceUTF8\Encoding::toUTF8($string);
        }
        
        return $string;
    }
    
   /**
    * Checks whether the specified string is an ASCII
    * string, without any special or UTF8 characters.
    * Note: empty strings and NULL are considered ASCII.
    * Any variable types other than strings are not.
    * 
    * @param mixed $string
    * @return boolean
    */
    public static function isStringASCII($string) : bool
    {
        if($string === '' || $string === NULL) {
            return true;
        }
        
        if(!is_string($string)) {
            return false;
        }
        
        return !preg_match('/[^\x00-\x7F]/', $string);
    }
    
    public static function highlight_url($url)
    {
        $url = htmlspecialchars($url);
        $url = str_replace(
            array('/', '='), 
            array('/<wbr>', '=<wbr>'), 
            $url
        );
        return $url;
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
    * @param array $options
    * @return float
    */
    public static function matchString($source, $target, $options=array())
    {
        $defaults = array(
            'maxLevenshtein' => 10,
            'precision' => 1
        );
        
        $options = array_merge($defaults, $options);
        
        // avoid doing this via levenshtein
        if($source == $target) {
            return 100;
        }
        
        $diff = levenshtein($source, $target);
        if($diff > $options['maxLevenshtein']) {
            return 0;
        }
        
        $percent = $diff * 100 / ($options['maxLevenshtein'] + 1);
        return round(100 - $percent, $options['precision']);
    }
    
   /**
    * Converts a date interval to a human readable string with
    * all necessary time components, e.g. "1 year, 2 months and 4 days".
    * 
    * @param \DateInterval $interval
    * @return string
    * @see ConvertHelper_IntervalConverter
    */
    public static function interval2string(\DateInterval $interval) : string
    {
        $converter = new ConvertHelper_IntervalConverter();
        return $converter->toString($interval);
    }
    
    const INTERVAL_DAYS = 'days';
    
    const INTERVAL_HOURS = 'hours';
    
    const INTERVAL_MINUTES = 'minutes';
    
    const INTERVAL_SECONDS = 'seconds';
    
   /**
    * Converts an interval to its total amount of days.
    * @param \DateInterval $interval
    * @return int
    */
    public static function interval2days(\DateInterval $interval) : int
    {
        return self::interval2total($interval, self::INTERVAL_DAYS);
    }

   /**
    * Converts an interval to its total amount of hours.
    * @param \DateInterval $interval
    * @return int
    */
    public static function interval2hours(\DateInterval $interval) : int
    {
        return self::interval2total($interval, self::INTERVAL_HOURS);
    }
    
   /**
    * Converts an interval to its total amount of minutes. 
    * @param \DateInterval $interval
    * @return int
    */
    public static function interval2minutes(\DateInterval $interval) : int
    {
        return self::interval2total($interval, self::INTERVAL_MINUTES);
    }
    
   /**
    * Converts an interval to its total amount of seconds.
    * @param \DateInterval $interval
    * @return int
    */    
    public static function interval2seconds(\DateInterval $interval) : int
    {
        return self::interval2total($interval, self::INTERVAL_SECONDS);
    }
    
   /**
    * Calculates the total amount of days / hours / minutes or seconds
    * of a date interval object (depending in the specified units), and 
    * returns the total amount.
    * 
    * @param \DateInterval $interval
    * @param string $unit What total value to calculate.
    * @return integer
    * 
    * @see ConvertHelper::INTERVAL_SECONDS
    * @see ConvertHelper::INTERVAL_MINUTES
    * @see ConvertHelper::INTERVAL_HOURS
    * @see ConvertHelper::INTERVAL_DAYS
    */
    public static function interval2total(\DateInterval $interval, $unit=self::INTERVAL_SECONDS) : int
    {
        $total = (int)$interval->format('%a');
        if ($unit == self::INTERVAL_DAYS) {
            return $total;
        }
        
        $total = ($total * 24) + ((int)$interval->h );
        if ($unit == self::INTERVAL_HOURS) {
            return $total;
        }
    
        $total = ($total * 60) + ((int)$interval->i );
        if ($unit == self::INTERVAL_MINUTES) {
            return $total;
        }

        $total = ($total * 60) + ((int)$interval->s );
        if ($unit == self::INTERVAL_SECONDS) {
            return $total;
        }
        
        return 0;
    }

    protected static $days;
    
    protected static $daysShort;

    protected static $daysInvariant = array(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    );
    
   /**
    * Converts a date to the corresponding day name.
    * 
    * @param \DateTime $date
    * @param bool $short
    * @return string|NULL
    */
    public static function date2dayName(\DateTime $date, bool $short=false)
    {
        $day = $date->format('l');
        $invariant = self::getDayNamesInvariant();
        
        $idx = array_search($day, $invariant);
        if($idx !== false) {
            $localized = self::getDayNames($short);
            return $localized[$idx];
        }
        
        return null;
    }
    
   /**
    * Retrieves a list of english day names.
    * @return string[]
    */
    public static function getDayNamesInvariant()
    {
        return self::$daysInvariant;
    }
    
   /**
    * Retrieves the day names list for the current locale.
    * 
    * @param bool $short
    * @return array
    */
    public static function getDayNames(bool $short=false) : array
    {
        if($short) {
            if(!isset(self::$daysShort)) {
                self::$daysShort = array(
                    t('Mon'),
                    t('Tue'),
                    t('Wed'),
                    t('Thu'),
                    t('Fri'),
                    t('Sat'),
                    t('Sun')
                );
            }
            
            return self::$daysShort;
        }
        
        if(!isset(self::$days)) {
            self::$days = array(
                t('Monday'),
                t('Tuesday'),
                t('Wednesday'),
                t('Thursday'),
                t('Friday'),
                t('Saturday'),
                t('Sunday')
            );
        }
        
        return self::$days;
    }

    /**
     * Implodes an array with a separator character, and the last item with "add".
     * 
     * @param array $list The indexed array with items to implode.
     * @param string $sep The separator character to use.
     * @param string $conjunction The word to use as conjunction with the last item in the list. NOTE: include spaces as needed.
     * @return string
     */
    public static function implodeWithAnd(array $list, $sep = ', ', $conjunction = null)
    {
        if(empty($list)) {
            return '';
        }
        
        if(empty($conjunction)) {
            $conjunction = t('and');
        }
        
        $last = array_pop($list);
        if($list) {
            return implode($sep, $list) . $conjunction . ' ' . $last;
        }
        
        return $last;
    }
    
   /**
    * Splits a string into an array of all characters it is composed of.
    * Unicode character safe.
    * 
    * NOTE: Spaces and newlines (both \r and \n) are also considered single
    * characters.
    * 
    * @param string $string
    * @return array
    */
    public static function string2array(string $string) : array
    {
        $result = preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY);
        if($result !== false) {
            return $result;
        }
        
        return array();
    }
    
   /**
    * Checks whether the specified string contains HTML code.
    * 
    * @param string $string
    * @return boolean
    */
    public static function isStringHTML(string $string) : bool
    {
        if(preg_match('%<[a-z/][\s\S]*>%siU', $string)) {
            return true;
        }
        
        $decoded = html_entity_decode($string);
        if($decoded !== $string) {
            return true;
        }
        
        return false;
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
        $wrapper = new ConvertHelper_WordWrapper();
        
        return $wrapper
        ->setLineWidth($width)
        ->setBreakCharacter($break)
        ->setCuttingEnabled($cut)
        ->wrapText($str);
    }
    
   /**
    * Calculates the byte length of a string, taking into 
    * account any unicode characters.
    * 
    * @param string $string
    * @return int
    * @see https://stackoverflow.com/a/9718273/2298192
    */
    public static function string2bytes($string)
    {
        return mb_strlen($string, '8bit');
    }
    
   /**
    * Creates a short, 8-character long hash for the specified string.
    * 
    * WARNING: Not cryptographically safe.
    * 
    * @param string $string
    * @return string
    */
    public static function string2shortHash($string)
    {
        return hash('crc32', $string, false);
    }
    
    public static function string2hash($string)
    {
        return md5($string);
    }
    
    public static function callback2string($callback) : string
    {
        return parseVariable($callback)->toString();
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
    * @return array
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
    * string is useful when doing a case insensitive search, as it 
    * shows the exact matched case of needle.
    *   
    * @param string $needle
    * @param string $haystack
    * @param bool $caseInsensitive
    * @return ConvertHelper_StringMatch[]
    */
    public static function findString(string $needle, string $haystack, bool $caseInsensitive=false)
    {
        if($needle === '') {
            return array();
        }
        
        $function = 'mb_strpos';
        if($caseInsensitive) {
            $function = 'mb_stripos';
        }
        
        $pos = 0;
        $positions = array();
        $length = mb_strlen($needle);
        
        while( ($pos = $function($haystack, $needle, $pos)) !== false) 
        {
            $match = mb_substr($haystack, $pos, $length);
            $positions[] = new ConvertHelper_StringMatch($pos, $match);
            $pos += $length;
        }
        
        return $positions;
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
        if(empty($string) || empty($delimiter)) {
            return array();
        }
        
        $tokens = explode($delimiter, $string);
        $tokens = array_map('trim', $tokens);
        
        $keep = array();
        foreach($tokens as $token) {
            if($token !== '') {
                $keep[] = $token;
            }
        }
        
        return $keep;
    }
    
    protected static $eolChars;

   /**
    * Detects the most used end-of-line character in the subject string.
    * 
    * @param string $str The string to check.
    * @return NULL|ConvertHelper_EOL The detected EOL instance, or NULL if none has been detected.
    */
    public static function detectEOLCharacter(string $subjectString) : ?ConvertHelper_EOL
    {
        if(empty($subjectString)) {
            return null;
        }
        
        if(!isset(self::$eolChars))
        {
            $cr = chr((int)hexdec('0d'));
            $lf = chr((int)hexdec('0a'));
            
           self::$eolChars = array(
               array(
                   'char' => $cr.$lf,
                   'type' => ConvertHelper_EOL::TYPE_CRLF,
                   'description' => t('Carriage return followed by a line feed'),
               ),
               array(
                   'char' => $lf.$cr,
                   'type' => ConvertHelper_EOL::TYPE_LFCR,
                   'description' => t('Line feed followed by a carriage return'),
               ),
               array(
                  'char' => $lf,
                  'type' => ConvertHelper_EOL::TYPE_LF,
                  'description' => t('Line feed'),
               ),
               array(
                  'char' => $cr,
                  'type' => ConvertHelper_EOL::TYPE_CR,
                  'description' => t('Carriage Return'),
               ),
            );
        }
        
        $max = 0;
        $results = array();
        foreach(self::$eolChars as $def) 
        {
            $amount = substr_count($subjectString, $def['char']);
            
            if($amount > $max)
            {
                $max = $amount;
                $results[] = $def;
            }
        }
        
        if(empty($results)) {
            return null;
        }
        
        return new ConvertHelper_EOL(
            $results[0]['char'], 
            $results[0]['type'],
            $results[0]['description']
        );
    }

   /**
    * Removes the specified keys from the target array,
    * if they exist.
    * 
    * @param array $array
    * @param array $keys
    */
    public static function arrayRemoveKeys(array &$array, array $keys) : void
    {
        foreach($keys as $key) 
        {
            if(array_key_exists($key, $array)) {
                unset($array[$key]); 
            }
        }
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
    * @return \DateInterval
    * @throws ConvertHelper_Exception If the date interval cannot be created.
    * 
    * @see ConvertHelper::ERROR_CANNOT_GET_DATE_DIFF
    */
    public static function seconds2interval(int $seconds) : \DateInterval
    {
        return ConvertHelper_DateInterval::fromSeconds($seconds)->getInterval();
    }
    
   /**
    * Converts a size string like "50 MB" to the corresponding byte size.
    * It is case insensitive, ignores spaces, and supports both traditional
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
}
