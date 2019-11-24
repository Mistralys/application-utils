<?php
/**
 * File containing the {@link ConvertHelper_EOL} class.
 * @package AppUtils
 * @subpackage ConvertHelper
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Container class for an end of line (EOL) character.
 *
 * @package AppUtils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see ConvertHelper::detectEOLCharacter()
 */
class ConvertHelper_EOL
{
    const TYPE_CRLF = 'CR+LF';
    const TYPE_LFCR = 'LF+CR';
    const TYPE_LF = 'LF';
    const TYPE_CR = 'CR';
    
   /**
    * @var string
    */
    protected $char;
    
   /**
    * @var string
    */
    protected $type;
    
   /**
    * @var string
    */
    protected $description;
    
    public function __construct(string $char, string $type, string $description)
    {
        $this->char = $char;
        $this->type = $type;
        $this->description = $description;
    }
    
   /**
    * The actual EOL character.
    * @return string
    */
    public function getCharacter() : string
    {
        return $this->char;
    }
    
   /**
    * Checks if the encoding matches the specified encoding string.
    * 
    * @param string $encoding
    * @return bool
    */
    public function isEncoding(string $encoding) : bool
    {
        return $this->encoding === $encoding;
    }

   /**
    * A more detailed, human readable description of the character.
    * @return string
    */
    public function getDescription() : string
    {
        return $this->description;
    }
    
   /**
    * The EOL character type, e.g. "CR+LF", "CR"...
    * @return string
    * 
    * @see ConvertHelper_EOL::TYPE_CR
    * @see ConvertHelper_EOL::TYPE_CRLF
    * @see ConvertHelper_EOL::TYPE_LF
    * @see ConvertHelper_EOL::TYPE_LFCR
    */
    public function getType() : string
    {
        return $this->type;
    }

    public function isCRLF() : bool
    {
        return $this->isType(self::TYPE_CRLF);
    }
    
    public function isCR() : bool
    {
        return $this->isType(self::TYPE_CR);
    }
    
    public function isLF() : bool
    {
        return $this->isType(self::TYPE_LF);
    }
    
    public function isLFCR() : bool
    {
        return $this->isType(self::TYPE_LFCR);
    }
    
    public function isType(string $type) : bool
    {
        return $this->type === $type;
    }
}
