<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_TabsNormalizer} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_TabsNormalizer
 */
declare(strict_types=1);

namespace AppUtils;

/**
 * Reduces tabbed indentation in a string so the text
 * is left-adjusted.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_TabsNormalizer
{
   /**
    * @var integer
    */
    protected $max = 0;
    
   /**
    * @var integer
    */
    protected $min = PHP_INT_MAX;
    
   /**
    * @var bool
    */
    protected $tabs2spaces = false;
    
   /**
    * @var string[]
    */
    protected $lines = array();

   /**
    * @var string
    */
    protected $eol = '';
    
   /**
    * @var integer
    */
    protected $tabSize = 4;
    
   /**
    * Whether to enable or disable the conversion
    * of tabs to spaces.
    * 
    * @param bool $enable
    * @return ConvertHelper_TabsNormalizer
    */
    public function convertTabsToSpaces(bool $enable=true) : ConvertHelper_TabsNormalizer
    {
        $this->tabs2spaces = $enable;
        
        return $this;
    }
    
   /**
    * Sets the size of a tab, in spaces. Used to convert tabs
    * from spaces and the other way around. Defaults to 4.
    * 
    * @param int $amountSpaces
    * @return ConvertHelper_TabsNormalizer
    */
    public function setTabSize(int $amountSpaces) : ConvertHelper_TabsNormalizer
    {
        $this->tabSize = $amountSpaces;
        
        return $this;
    }
    
   /**
    * Normalizes tabs in the specified string by indenting everything
    * back to the minimum tab distance. With the second parameter,
    * tabs can optionally be converted to spaces as well (recommended
    * for HTML output).
    *
    * @param string $string
    * @return string
    */
    public function normalize(string $string) : string
    {
        $this->splitLines($string);
        $this->countOccurrences();
        
        $result = $this->_normalize();
        
        if($this->tabs2spaces) 
        {
            $result = ConvertHelper::tabs2spaces($result, $this->tabSize);
        }
        
        $this->lines = array(); // clear memory
        
        return $result;
    }
    
    protected function splitLines(string $string) : void
    {
        $eol = ConvertHelper::detectEOLCharacter($string);
        
        if($eol !== null) 
        {
            $this->eol = $eol->getCharacter();
        }
        
        // convert any existing space based tabs to actual tabs
        $string = ConvertHelper::spaces2tabs($string, $this->tabSize);
        
        // explode only using \n, as the lines will be trimmed and
        // then imploded again with the EOL char: this way it is EOL
        // independent.
        $this->lines = explode("\n", $string);
    }
    
    protected function _normalize() : string
    {
        $converted = array();
        
        foreach($this->lines as $line) 
        {
            $amount = substr_count($line, "\t") - $this->min;
            
            $line = trim($line, "\n\r\t");
            
            if($amount >= 1) 
            {
                $line = str_repeat("\t", $amount) . $line;
            }
            
            $converted[] = $line;
        }
        
        return implode($this->eol, $converted);
    }
    
   /**
    * Finds out the minimum and maximum amount of 
    * tabs in the string.
    */
    protected function countOccurrences() : void
    {
        foreach($this->lines as $line) 
        {
            $amount = substr_count($line, "\t");
            
            if($amount > $this->max) 
            {
                $this->max = $amount;
                continue;
            }
            
            if($amount > 0 && $amount < $this->min) 
            {
                $this->min = $amount;
            }
        }
        
        if($this->min === PHP_INT_MAX) {
            $this->min = 0;
        }
    }
}
