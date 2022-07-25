<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\URIParser
 */

declare(strict_types=1);

namespace AppUtils\URLInfo;

use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\URLInfo\Parser\BaseURLTypeDetector;
use AppUtils\URLInfo\Parser\BaseURLValidator;
use AppUtils\URLInfo\Parser\ParsedInfoFilter;
use AppUtils\URLInfo\Parser\URLTypeDetector\DetectEmail;
use AppUtils\URLInfo\Parser\URLTypeDetector\DetectFragmentLink;
use AppUtils\URLInfo\Parser\URLTypeDetector\DetectIPAddress;
use AppUtils\URLInfo\Parser\URLTypeDetector\DetectPhoneLink;
use AppUtils\URLInfo\Parser\URLTypeDetector\DetectStandardURL;
use AppUtils\URLInfo\Parser\URLValidator\ValidateHostIsPresent;
use AppUtils\URLInfo\Parser\URLValidator\ValidateIsTypeKnown;
use AppUtils\URLInfo\Parser\URLValidator\ValidateSchemeIsKnown;
use AppUtils\URLInfo\Parser\URLValidator\ValidateSchemeIsSet;

/**
 * Handles the URL parsing, as replacement for PHP's 
 * native parse_url function. It overcomes a number of
 * limitations of the function, using pre- and post-processing
 * of the URL and its component parts.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URIParser
{
    use URLInfoTrait;

    protected string $url;
    protected bool $isValid = false;
    protected bool $encodeUTF = false;

   /**
    * @var array{code:int,message:string}|NULL
    */
    protected ?array $error = null;
    
   /**
    * Stores a list of all unicode characters in the URL
    * that have been filtered out before parsing it with
    * parse_url.
    * 
    * @var array<string,string>
    */
    protected array $unicodeChars = array();

    /**
     * @var class-string[]
     */
    private static array $detectorClasses = array(
        DetectEmail::class,
        DetectFragmentLink::class,
        DetectPhoneLink::class,
        DetectIPAddress::class,
        DetectStandardURL::class
    );

    /**
     * @var class-string[]
     */
    private static array $validatorClasses = array(
        ValidateIsTypeKnown::class,
        ValidateSchemeIsSet::class,
        ValidateSchemeIsKnown::class,
        ValidateHostIsPresent::class
    );

    /**
    * 
    * @param string $url The target URL.
    * @param bool $encodeUTF Whether to URL encode any plain text unicode characters.
    */
    public function __construct(string $url, bool $encodeUTF)
    {
        $this->url = $url;
        $this->encodeUTF = $encodeUTF;

        $this->parse();
        $this->detectType();
        $this->validate();
    }

   /**
    * Retrieves the array as parsed by PHP's parse_url,
    * filtered and adjusted as necessary.
    * 
    * @return array<string,mixed>
    */
    public function getInfo() : array
    {
        return $this->info;
    }

    protected function parse() : void
    {
        $this->filterUnicodeChars();
        
        $this->info = parse_url($this->url);

        $this->filterParsed();

        // if the URL contains any URL characters, and we
        // do not want them URL encoded, restore them.
        if(!$this->encodeUTF && !empty($this->unicodeChars))
        {
            $this->info = $this->restoreUnicodeChars($this->info);
        }
    }

   /**
    * Finds any non-url encoded unicode characters in 
    * the URL, and encodes them before the URL is 
    * passed to parse_url.
    */
    protected function filterUnicodeChars() : void
    {
        $chars = ConvertHelper::string2array($this->url);
        
        $keep = array();
        
        foreach($chars as $char)
        {
            if(preg_match('/\p{L}/uix', $char))
            {
                $encoded = rawurlencode($char);
                
                if($encoded !== $char)
                {
                    $this->unicodeChars[$encoded] = $char;
                    $char = $encoded;
                }
            }
            
            $keep[] = $char;
        }
        
        $this->url = implode('', $keep);
    }

    protected function detectType() : bool
    {
        foreach(self::$detectorClasses as $className)
        {
            $detector = ClassHelper::requireObjectInstanceOf(
                BaseURLTypeDetector::class,
                new $className($this)
            );

            $detected = $detector->detect();

            // Use the adjusted data
            $this->info = $detector->getInfo();

            if($detected) {
                $this->isValid = true;
                return true;
            }
        }

        return false;
    }

    protected function validate() : void
    {
        foreach(self::$validatorClasses as $validatorClass)
        {
            $validator = ClassHelper::requireObjectInstanceOf(
                BaseURLValidator::class,
                new $validatorClass($this)
            );

            $result = $validator->validate();

            $this->info = $validator->getInfo();

            if($result !== true) {
                $this->isValid = false;
                return;
            }
        }
        
        $this->isValid = true;
    }

   /**
    * Goes through all information in the parse_url result
    * array, and attempts to fix any user errors in formatting
    * that can be recovered from, mostly regarding stray spaces.
    */
    protected function filterParsed() : void
    {
        $this->info = (new ParsedInfoFilter($this->url, $this->info))->filter();
    }
    
   /**
    * Recursively goes through the array, and converts all previously
    * URL encoded characters with their unicode character counterparts.
    * 
    * @param array<string,mixed> $subject
    * @return array<string,mixed>
    */
    protected function restoreUnicodeChars(array $subject) : array
    {
        $result = array();
        
        foreach($subject as $key => $val)
        {
            if(is_array($val))
            {
                $val = $this->restoreUnicodeChars($val);
            }
            else
            {
                $val = $this->restoreUnicodeChar($val);
            }
            
            $key = $this->restoreUnicodeChar($key);
            
            $result[$key] = $val;
        }
        
        return $result;
    }
    
   /**
    * Replaces all URL encoded unicode characters
    * in the string with the unicode character.
    * 
    * @param string $string
    * @return string
    */
    protected function restoreUnicodeChar(string $string) : string
    {
        if(strpos($string, '%') !== false)
        {
            return str_replace(array_keys($this->unicodeChars), array_values($this->unicodeChars), $string);
        }
        
        return $string;
    }

    public function setError(int $code, string $message) : void
    {
        $this->isValid = false;
        
        $this->error = array(
            'code' => $code,
            'message' => $message
        );
    }
   
   /**
    * Checks whether the URL that was parsed is valid.
    * @return bool
    */
    public function isValid() : bool
    {
        return $this->isValid;
    }

   /**
    * If the validation failed, retrieves the validation
    * error message.
    * 
    * @return string
    */
    public function getErrorMessage() : string
    {
        return $this->error['message'] ?? '';
    }
    
   /**
    * If the validation failed, retrieves the validation
    * error code.
    * 
    * @return int
    */
    public function getErrorCode() : int
    {
        return $this->error['code'] ?? -1;
    }
}
