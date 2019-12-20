<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_StringMatch} class.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see ConvertHelper_StringMatch
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Container for an individual occurrence of a string
 * that was found in a haystack using the method
 * {@link ConvertHelper::findString()}.
 *  
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see ConvertHelper::findString()
 */
class ConvertHelper_StringMatch
{
    protected $position;
    
    protected $match;
    
    public function __construct(int $position, string $matchedString)
    {
        $this->position = $position;
        $this->match = $matchedString;
    }
    
   /**
    * The zero-based start position of the string in the haystack.
    * @return int
    */
    public function getPosition() : int
    {
        return $this->position;
    }
    
   /**
    * The exact string that was matched, respecting the case as found in needle.
    * @return string
    */
    public function getMatchedString() : string
    {
        return $this->match;
    }
}
