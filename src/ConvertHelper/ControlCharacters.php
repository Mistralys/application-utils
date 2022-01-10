<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_ControlCharacters} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_ControlCharacters
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Control characters management class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_ControlCharacters
{
    public const ERROR_MALFORMATTED_STRING = 53801;
    
   /**
    * @var string[]
    */
    protected static $controlChars =  array(
        '0000-0008', // control chars
        '000E-000F', // control chars
        '0010-001F', // control chars
        '2000-200F', // non-breaking space and co
    );
    
   /**
    * @var string|NULL
    */
    protected static $controlCharsRegex;

   /**
    * @var string[]
    */
    protected static $hexAlphabet = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
    
   /**
    * @var string[]|NULL
    */
    protected static $charsAsHex;
    
    public function __construct()
    {
        // create the regex from the unicode characters list
        if(!isset(self::$controlCharsRegex))
        {
            $chars = $this->getCharsAsHex();
            
            // we use the notation \x{0000} to specify the unicode character key
            // in the regular expression.
            $stack = array();
            
            foreach($chars as $char) 
            {
                $stack[] = '\x{'.$char.'}';
            }
            
            self::$controlCharsRegex = '/['.implode('', $stack).']/u';
        }
    }
    
   /**
    * Retrieves the HEX character codes for all control
    * characters that the {@link stripControlCharacters()}
    * method will remove.
    *
    * @return string[]
    */
    public function getCharsAsHex() : array
    {
        if (isset(self::$charsAsHex))
        {
            return self::$charsAsHex;
        }
        
        $stack = array();
        
        foreach(self::$controlChars as $char)
        {
            $tokens = explode('-', $char);
            $start = $tokens[0];
            $end = $tokens[1];
            $prefix = substr($start, 0, 3);
            
            $range = array();
            
            foreach(self::$hexAlphabet as $number) 
            {
                $range[] = $prefix.$number;
            }
            
            $use = false;
            
            foreach($range as $number) 
            {
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
        
        self::$charsAsHex = $stack;
        
        return $stack;
    }
    
   /**
    * Retrieves an array of all control characters that
    * the {@link stripControlCharacters()} method will
    * remove, as the actual UTF-8 characters.
    *
    * @return string[]
    */
    public function getCharsAsUTF8() : array
    {
        $chars = $this->getCharsAsHex();
        
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
    public function getCharsAsJSON() : array
    {
        $chars = $this->getCharsAsHex();
        
        $result = array();
        foreach($chars as $char) {
            $result[] = '\u'.strtolower($char);
        }
        
        return $result;
    }
    
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
    public function stripControlCharacters(string $string) : string
    {
        if(empty($string)) 
        {
            return $string;
        }
        
        $result = preg_replace(self::$controlCharsRegex, '', $string);
        
        // can happen if the text contains invalid UTF8
        if($result === null)
        {
            $string = ConvertHelper::string2utf8($string);
            
            $result = preg_replace(self::$controlCharsRegex, '', $string);
            
            if($result === null)
            {
                throw new ConvertHelper_Exception(
                    'Cannot strip control characters: malformatted string encountered.',
                    'preg_replace returned null, which happens when a string contains broken unicode characters. Tried to fix the string, but this did not help.',
                    self::ERROR_MALFORMATTED_STRING
                );
            }
        }
        
        return (string)$result;
    }
}
