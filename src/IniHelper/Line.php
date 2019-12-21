<?php
/**
 * File containing the {@link IniHelper_Line} class.
 * @package Application Utils
 * @subpackage IniHelper
 * @see IniHelper_Line
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Single INI line container.
 *
 * @package Application Utils
 * @subpackage IniHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class IniHelper_Line
{
    const TYPE_SECTION_DECLARATION = 'section';
    
    const TYPE_COMMENT = 'comment';
    
    const TYPE_EMPTY = 'empty';
    
    const TYPE_INVALID = 'invalid';
    
    const TYPE_VALUE = 'value';
    
    const ERROR_UNHANDLED_LINE_TYPE = 41901;
    
    const ERROR_NON_SCALAR_VALUE = 41902;
    
    /**
     * @var string
     */
    protected $text;
    
   /**
    * @var string
    */
    protected $trimmed;
    
   /**
    * @var int
    */
    protected $lineNumber;
    
   /**
    * @var string
    */
    protected $type;
    
   /**
    * @var string
    */
    protected $varName = '';
    
   /**
    * @var string
    */
    protected $varValue = '';
    
    protected $valueUnquoted = '';
    
    protected $quoteStyle = '';
    
   /**
    * @var string
    */
    protected $sectionName = '';
    
    public function __construct(string $text, int $lineNumber)
    {
        $this->text = $text;
        $this->trimmed = trim($text);
        $this->lineNumber = $lineNumber;
        
        if(empty($this->trimmed)) 
        {
            $this->type = self::TYPE_EMPTY;
            return;
        }
        
        $startChar = substr($this->trimmed, 0, 1);
        
        if($startChar === ';')
        {
            $this->type = self::TYPE_COMMENT;
        }
        else if($startChar === '[')
        {
            $this->type = self::TYPE_SECTION_DECLARATION;
            $this->sectionName = trim($this->trimmed, '[]');
            $this->sectionName = trim($this->sectionName); // remove any whitespace
        }
        else
        {
            $pos = strpos($this->trimmed, '=');
            if($pos === false) 
            {
                $this->type = self::TYPE_INVALID;
                return;
            }
            
            $this->type = self::TYPE_VALUE;
            $this->varName = trim(substr($this->trimmed, 0, $pos));
            
            $this->parseValue(substr($this->trimmed, $pos+1));
        }
    }
    
    protected function parseValue(string $value)
    {
        $this->varValue = trim($value);
        
        $value = $this->varValue;
        
        if(substr($value, 0, 1) == '"' && substr($value, -1, 1) == '"')
        {
            $value = trim($value, '"');
            $this->quoteStyle = '"';
        }
        else if(substr($value, 0, 1) == "'" && substr($value, -1, 1) == "'")
        {
            $value = trim($value, "'");
            $this->quoteStyle = "'";
        }
        
        $this->valueUnquoted = $value;
    }
    
    public function getVarName() : string
    {
        return $this->varName;
    }
    
    public function getVarValue() : string
    {
        return $this->valueUnquoted;
    }
    
    public function getQuotedVarValue() : string
    {
        if($this->quoteStyle === '') {
            return $this->getVarValue();
        }
        
        return $this->quoteStyle.$this->getVarValue().$this->quoteStyle;
    }
    
    public function getText() : string
    {
        return $this->text;
    }
    
    public function getLineNumber() : int
    {
        return $this->lineNumber;
    }
    
    public function getSectionName() : string
    {
        return $this->sectionName;
    }
    
    public function isSection() : bool
    {
        return $this->isType(self::TYPE_SECTION_DECLARATION);
    }
    
    public function isComment() : bool
    {
        return $this->isType(self::TYPE_COMMENT);
    }
    
    public function isValue() : bool
    {
        return $this->isType(self::TYPE_VALUE);
    }
    
    public function isValid() : bool
    {
        return !$this->isType(self::TYPE_INVALID);
    }
    
    public function isEmpty() : bool
    {
        return $this->isType(self::TYPE_EMPTY);
    }
    
    protected function isType(string $type) : bool
    {
        return $this->type === $type;
    }
    
    public function setValue($value) : IniHelper_Line
    {
        if(!is_scalar($value)) 
        {
            throw new IniHelper_Exception(
                'Cannot use non-scalar values.',
                sprintf(
                    'Tried setting the value of [%s] to [%s]',
                    $this->getVarName(),
                    parseVariable($value)->toString()
                ),
                self::ERROR_NON_SCALAR_VALUE
            );
        }
        
        $this->parseValue((string)$value);
        
        return $this;
    }
    
    public function toString() : string
    {
        switch($this->type) 
        {
            case self::TYPE_EMPTY:
            case self::TYPE_INVALID:
                return '';
                
            case self::TYPE_COMMENT:
                return $this->text;
                
            case self::TYPE_SECTION_DECLARATION:
                return '['.$this->getSectionName().']';
                
            case self::TYPE_VALUE:
                $string = $this->getVarName().'='.$this->getQuotedVarValue();
                return $string;
        }
        
        throw new IniHelper_Exception(
            'Unhandled line type',
            sprintf(
                'The line type [%s] is not handled for converting to string.',
                $this->type
            ),
            self::ERROR_UNHANDLED_LINE_TYPE
        );
    }
}
