<?php
/**
 * File containing the {@link \AppUtils\IniHelper\INILine} class.
 * @package Application Utils
 * @subpackage IniHelper
 * @see \AppUtils\IniHelper\INILine
 */

declare(strict_types=1);

namespace AppUtils\IniHelper;

use AppUtils\IniHelper_Exception;
use function AppUtils\parseVariable;

/**
 * Single INI line container.
 *
 * @package Application Utils
 * @subpackage IniHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class INILine
{
    public const TYPE_SECTION_DECLARATION = 'section';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_EMPTY = 'empty';
    public const TYPE_INVALID = 'invalid';
    public const TYPE_VALUE = 'value';
    
    public const ERROR_UNHANDLED_LINE_TYPE = 41901;
    public const ERROR_NON_SCALAR_VALUE = 41902;
    
    protected string $text;
    protected string $trimmed;
    protected int $lineNumber;
    protected string $type;
    protected string $varName = '';
    protected string $varValue = '';
    protected string $valueUnquoted = '';
    protected string $quoteStyle = '';
    protected string $sectionName = '';
    
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
        
        $startChar = $this->trimmed[0];
        
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
    
    protected function parseValue(string $value) : void
    {
        $this->varValue = trim($value);
        $value = $this->varValue;

        if(empty($value)) {
            return;
        }
        
        if($value[0] === '"' && $value[strlen($value) - 1] === '"')
        {
            $value = trim($value, '"');
            $this->quoteStyle = '"';
        }
        else if($value[0] === "'" && $value[strlen($value) - 1] === "'")
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

    /**
     * @param mixed|NULL $value
     * @return $this
     * @throws IniHelper_Exception
     */
    public function setValue($value) : INILine
    {
        if(!is_null($value) && !is_scalar($value))
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
                return $this->getVarName().'='.$this->getQuotedVarValue();
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
