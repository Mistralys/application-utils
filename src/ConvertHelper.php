<?php

namespace AppUtils;

class ConvertHelper
{
    const ERROR_STRIPCONTROLCHARS_NOT_STRING = 23301;
    
    const ERROR_NORMALIZETABS_INVALID_PARAMS = 23302;
    
    const ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER = 23303;
    
    const ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE = 23304;
    
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
    public static function normalizeTabs($string, $tabs2spaces = false)
    {
        if (!is_string($string)) {
            throw new ConvertHelper_Exception(
                'Invalid parameters',
                sprintf(
                    'Argument for normalizing tabs is not a string, %1$s given.',
                    gettype($string)
                ),
                self::ERROR_NORMALIZETABS_INVALID_PARAMS
            );
        }

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
     * @param float|\DateTime $datefrom
     * @param float|\DateTime $dateto
     * @link http://www.sajithmr.com/php-time-ago-calculation/
     */
    public static function duration2string($datefrom, $dateto = -1)
    {
        if($datefrom instanceof \DateTime) {
            $datefrom = ConvertHelper::date2timestamp($datefrom);
        }
        
        if($dateto instanceof \DateTime) {
            $dateto = ConvertHelper::date2timestamp($dateto);
        }
        
        // Defaults and assume if 0 is passed in that
        // its an error rather than the epoch

        if ($datefrom <= 0) {
            return t('A long time ago');
        }
        if ($dateto == -1) {
            $dateto = time();
        }

        // Calculate the difference in seconds betweeen
        // the two timestamps

        $difference = $dateto - $datefrom;
        
        $future = false;
        if($difference < 0) {
            $difference = $difference * -1;
            $future = true;
        }

        // If difference is less than 60 seconds,
        // seconds is a good interval of choice

        if ($difference < 60) {
            $interval = "s";
        }

        // If difference is between 60 seconds and
        // 60 minutes, minutes is a good interval
        elseif ($difference >= 60 && $difference < 60 * 60) {
            $interval = "n";
        }

        // If difference is between 1 hour and 24 hours
        // hours is a good interval
        elseif ($difference >= 60 * 60 && $difference < 60 * 60 * 24) {
            $interval = "h";
        }

        // If difference is between 1 day and 7 days
        // days is a good interval
        elseif ($difference >= 60 * 60 * 24 && $difference < 60 * 60 * 24 * 7) {
            $interval = "d";
        }

        // If difference is between 1 week and 30 days
        // weeks is a good interval
        elseif ($difference >= 60 * 60 * 24 * 7 && $difference < 60 * 60 * 24 * 30) {
            $interval = "ww";
        }

        // If difference is between 30 days and 365 days
        // months is a good interval, again, the same thing
        // applies, if the 29th February happens to exist
        // between your 2 dates, the function will return
        // the 'incorrect' value for a day
        elseif ($difference >= 60 * 60 * 24 * 30 && $difference < 60 * 60 * 24 * 365) {
            $interval = "m";
        }

        // If difference is greater than or equal to 365
        // days, return year. This will be incorrect if
        // for example, you call the function on the 28th April
        // 2008 passing in 29th April 2007. It will return
        // 1 year ago when in actual fact (yawn!) not quite
        // a year has gone by
        elseif ($difference >= 60 * 60 * 24 * 365) {
            $interval = "y";
        }

        // Based on the interval, determine the
        // number of units between the two dates
        // From this point on, you would be hard
        // pushed telling the difference between
        // this function and DateDiff. If the $datediff
        // returned is 1, be sure to return the singular
        // of the unit, e.g. 'day' rather 'days'
        switch ($interval) {
            case "m":
                $months_difference = floor($difference / 60 / 60 / 24 / 29);
                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }
                $datediff = $months_difference;

                // We need this in here because it is possible
                // to have an 'm' interval and a months
                // difference of 12 because we are using 29 days
                // in a month
                if ($datediff == 12) {
                    $datediff--;
                }

                if($future) {
                    $res = ($datediff == 1) ? t('In one month', $datediff) : t('In %1s months', $datediff);
                } else {
                    $res = ($datediff == 1) ? t('One month ago', $datediff) : t('%1s months ago', $datediff);
                }
                break;

            case "y":
                $datediff = floor($difference / 60 / 60 / 24 / 365);
                if($future) {
                    $res = ($datediff == 1) ? t('In one year', $datediff) : t('In %1s years', $datediff);
                } else {
                    $res = ($datediff == 1) ? t('One year ago', $datediff) : t('%1s years ago', $datediff);
                }
                break;

            case "d":
                $datediff = floor($difference / 60 / 60 / 24);
                if($future) {
                    $res = ($datediff == 1) ? t('In one day', $datediff) : t('In %1s days', $datediff);
                } else {
                    $res = ($datediff == 1) ? t('One day ago', $datediff) : t('%1s days ago', $datediff);
                }
                break;

            case "ww":
                $datediff = floor($difference / 60 / 60 / 24 / 7);
                if($future) {
                    $res = ($datediff == 1) ? t('In one week', $datediff) : t('In %1s weeks', $datediff);
                } else {
                    $res = ($datediff == 1) ? t('One week ago', $datediff) : t('%1s weeks ago', $datediff);
                }
                break;

            case "h":
                $datediff = floor($difference / 60 / 60);
                if($future) {
                    $res = ($datediff == 1) ? t('In one hour', $datediff) : t('In %1s hours', $datediff);
                } else {
                    $res = ($datediff == 1) ? t('One hour ago', $datediff) : t('%1s hours ago', $datediff);
                }
                break;

            case "n":
                $datediff = floor($difference / 60);
                if($future) {
                    $res = ($datediff == 1) ? t('In one minute', $datediff) : t('In %1s minutes', $datediff);
                } else {
                    $res = ($datediff == 1) ? t('One minute ago', $datediff) : t('%1s minutes ago', $datediff);
                }
                break;

            case "s":
                $datediff = $difference;
                if($future) {
                    $res = ($datediff == 1) ? t('In one second', $datediff) : t('In %1s seconds', $datediff);
                } else {
                    $res = ($datediff == 1) ? t('One second ago', $datediff) : t('%1s seconds ago', $datediff);
                }
                break;
        }

        return $res;
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
     * @param $bytes
     * @param $precision
     * @return string
     */
    public static function bytes2readable($bytes, $precision = 1)
    {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes . ' ' . t('B');

        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $precision) . ' ' . t('Kb');

        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $precision) . ' ' . t('Mb');

        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $precision) . ' ' . t('Gb');

        } elseif ($bytes >= $terabyte) {
            return round($bytes / $gigabyte, $precision) . ' ' . t('Tb');
        }

        return $bytes . ' ' . t('B');
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
            $label = $date->format('d') . '. ' . self::month2string($date->format('m'), $shortMonth) . ' ';
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
     * @param int $monthNr
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
    public static function stripControlCharacters($string)
    {
        if(empty($string)) {
            return $string;
        }
        
        if(!is_string($string)) {
            throw new ConvertHelper_Exception(
                'Subject is not a string',
                sprintf(
                    'Expected string, [%s] given.',
                    gettype($string)
                ),
                self::ERROR_STRIPCONTROLCHARS_NOT_STRING
            );
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
    
    public static function areVariablesEqual($a, $b)
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
    public static function areStringsEqual($a, $b)
    {
        return self::areVariablesEqual($a, $b);
    }

    /**
     * Checks whether the two specified numbers are equal.
     * null and empty strings are considered as 0 values.
     *
     * @param number $a
     * @param number $b
     * @return boolean
     */
    public static function areNumbersEqual($a, $b)
    {
        return self::areVariablesEqual($a, $b);
    }

    /**
     * Converts a boolean value to a string. Defaults to returning
     * 'true' or 'false', with the additional parameter it can also
     * return the 'yes' and 'no' variants.
     *
     * @param boolean $boolean
     * @param boolean $yesno
     * @return string
     */
    public static function bool2string($boolean, $yesno = false)
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
    * @param string|boolean $value
    * @return boolean
    */
    public static function isBoolean($value)
    {
        if(is_bool($value)) {
            return true;
        }
        
        return array_key_exists($value, self::$booleanStrings);
    }
    
   /**
    * Converts an associative array to an HTML style attribute value string.
    * 
    * @param string $subject
    * @return string
    */
    public static function array2styleString($subject)
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
    public static function date2timestamp(\DateTime $date)
    {
        return $date->format('U');
    }
    
   /**
    * Converts a timestamp into a DateTime instance.
    * @param int $timestamp
    * @return \DateTime
    */
    public static function timestamp2date($timestamp)
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
    */
    public static function fileRelativize($path)
    {
        $path = str_replace('\\', '/', $path);
        $root = str_Replace('\\', '/', APP_ROOT);
        return str_replace($root, '', $path);
    }
    
    const JS_REGEX_OBJECT = 'object';
    
    const JS_REGEX_JSON = 'json';
    
   /**
    * Takes a regular expression and attempts to convert it to
    * its javascript equivalent. Returns an array containing the
    * format string itself (without start and end characters),
    * and the modifiers.
    *  
    * This is intended to be used with the RegExp object, for ex:
    * 
    * <script>
    * var expression = <?php echo json_encode(ConvertHelper::regex2js('/ab+c/i')) ?>;
    * var reg = new RegExp(expression.format, expression.modifiers);
    * </script>
    *  
    * @param string $regex
    * @return array
    */
    public static function regex2js($regex, $return=self::JS_REGEX_OBJECT)
    {
        $regex = trim($regex);
        $separator = substr($regex, 0, 1);
        $parts = explode($separator, $regex);
        array_shift($parts);
        
        $modifiers = array_pop($parts);
        if($modifiers == $separator) {
            $modifiers = '';
        }
        
        $modifierReplacements = array(
            's' => '',
            'U' => ''
        );
        
        $modifiers = str_replace(array_keys($modifierReplacements), array_values($modifierReplacements), $modifiers);
        
        $format = implode($separator, $parts);
        
        // convert the anchors that are not supported in js regexes
        $format = str_replace(array('\\A', '\\Z', '\\z'), array('^', '$', ''), $format);
        
        if($return==self::JS_REGEX_JSON) {
            return json_encode(array(
                'format' => $format,
                'modifiers' => $modifiers
            ));
        }
        
        if(!empty($modifiers)) {
            return sprintf(
                'new RegExp(%s, %s)',
                json_encode($format),
                json_encode($modifiers)
            );
        }
        
        return sprintf(
            'new RegExp(%s)',
            json_encode($format)
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
    * @param string $string
    * @return boolean
    */
    public static function isStringASCII($string)
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
    
    public static function interval2string(\DateInterval $interval)
    {
        $tokens = array('y', 'm', 'd', 'h', 'i', 's');
        
        $offset = 0;
        $keep = array();
        foreach($tokens as $token) {
            if($interval->$token > 0) {
                $keep = array_slice($tokens, $offset);
                break;
            }
            
            $offset++;
        }
        
        $parts = array();
        foreach($keep as $token) 
        {
            $value = $interval->$token;
            $label = '';
            
            $suffix = 'p';
            if($value == 1) { $suffix = 's'; }
            $token .= $suffix;
            
            switch($token) {
                case 'ys': $label = t('1 year'); break;
                case 'yp': $label = t('%1$s years', $value); break;
                case 'ms': $label = t('1 month'); break;
                case 'mp': $label = t('%1$s months', $value); break;
                case 'ds': $label = t('1 day'); break;
                case 'dp': $label = t('%1$s days', $value); break;
                case 'hs': $label = t('1 hour'); break;
                case 'hp': $label = t('%1$s hours', $value); break;
                case 'is': $label = t('1 minute'); break;
                case 'ip': $label = t('%1$s minutes', $value); break;
                case 'ss': $label = t('1 second'); break;
                case 'sp': $label = t('%1$s seconds', $value); break;
            }
            
            $parts[] = $label;
        }
        
        if(count($parts) == 1) {
            return $parts[0];
        } 
        
        $last = array_pop($parts);
        
        return t('%1$s and %2$s', implode(', ', $parts), $last);
    }
    
    const INTERVAL_DAYS = 'days';
    
    const INTERVAL_HOURS = 'hours';
    
    const INTERVAL_MINUTES = 'minutes';
    
    const INTERVAL_SECONDS = 'seconds';
    
   /**
    * Calculates the total amount of days / hours / minutes or seconds
    * of a date interval object and returns the value.
    * 
    * @param \DateInterval $interval
    * @param string $unit
    * @return integer
    */
    public static function interval2total(\DateInterval $interval, $unit=self::INTERVAL_SECONDS)
    {
        $total = $interval->format('%a');
        if ($unit == self::INTERVAL_DAYS) {
            return $total;
        }

        $total = ($total * 24) + ($interval->h );
        if ($unit == self::INTERVAL_HOURS) {
            return $total;
        }
    
        $total = ($total * 60) + ($interval->i );
        if ($unit == self::INTERVAL_MINUTES)
            return $total;

        $total = ($total * 60) + ($interval->s );
        if ($unit == self::INTERVAL_SECONDS)
            return $total;
        
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
    * @param string $short
    * @return string|NULL
    */
    public static function date2dayName(\DateTime $date, $short=false)
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
    * @param string $short
    * @return string[]
    */
    public static function getDayNames($short=false)
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
    * Spaces and newlines (both \r and \n) are also considered single
    * characters. UTF-8 character safe.
    * 
    * @param string $string
    * @return string[]
    */
    public static function string2array($string)
    {
        return preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY);
    }
    
   /**
    * Checks whether the specified string contains HTML code.
    * 
    * @param string $string
    * @return boolean
    */
    public static function isStringHTML($string)
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
    * are no calculated correctly.
    * 
    * @param string $str
    * @param int $width
    * @param string $break
    * @param bool $cut
    * @return string
    * @see https://stackoverflow.com/a/4988494/2298192
    */
    public static function wordwrap($str, $width = 75, $break = "\n", $cut = false) 
    {
        $lines = explode($break, $str);
        
        foreach ($lines as &$line) 
        {
            $line = rtrim($line);
            if (mb_strlen($line) <= $width) {
                continue;
            }
        
            $words = explode(' ', $line);
            $line = '';
            $actual = '';
            foreach ($words as $word) 
            {
                if (mb_strlen($actual.$word) <= $width) 
                {
                    $actual .= $word.' ';
                } 
                else 
                {
                    if ($actual != '') {
                        $line .= rtrim($actual).$break;
                    }
                    
                    $actual = $word;
                    if ($cut) 
                    {
                        while (mb_strlen($actual) > $width) {
                            $line .= mb_substr($actual, 0, $width).$break;
                            $actual = mb_substr($actual, $width);
                        }
                    }
                    
                    $actual .= ' ';
                }
            }
            
            $line .= trim($actual);
        }
        
        return implode($break, $lines);
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
        return new ConvertHelper_ThrowableInfo($e);
    }
}
