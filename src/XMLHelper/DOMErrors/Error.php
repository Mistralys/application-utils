<?php
/**
 * File containing the {@see AppUtils\XMLHelper_DOMErrors_Error} class.
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper_DOMErrors_Error
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * LibXML error wrapper: offers easy access to an error's 
 * iformation.
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class XMLHelper_DOMErrors_Error
{
    const ERROR_CANNOT_UNSERIALIZE_ERROR_DATA = 57201;
    const ERROR_ERROR_DATA_KEY_MISSING = 57202;
    
   /**
    * @var \LibXMLError
    */
    private $error;
    
    private static $requiredKeys = array(
        'code',
        'column',
        'level',
        'message',
        'line'
    );
    
    public function __construct(\LibXMLError $error)
    {
        $this->error = $error;
    }
    
    public static function fromSerialized(string $serialized) : XMLHelper_DOMErrors_Error
    {
        $data = @json_decode($serialized, true);
        
        if(!is_array($data))
        {
            throw new XMLHelper_Exception(
                'Could not unserialize error data',
                sprintf(
                    'The specified serialized error data could not be desrialized. Source string: [%s]',
                    $serialized
                ),
                self::ERROR_CANNOT_UNSERIALIZE_ERROR_DATA
            );
        }
        
        self::checkErrorData($data);
        
        $error = new \LibXMLError();
        $error->code = (int)$data['code'];
        $error->column = (int)$data['column'];
        $error->level = (int)$data['level'];
        $error->message = (string)$data['message'];
        $error->line = (int)$data['line'];
        
        return new XMLHelper_DOMErrors_Error($error);
    }
    
    private static function checkErrorData(array $data) : void
    {
        foreach(self::$requiredKeys as $key)
        {
            if(!array_key_exists($key, $data))
            {
                throw new XMLHelper_Exception(
                    'Required key missing in error data',
                    sprintf(
                        'The key [%s] is not present in the error data. Existing keys are [%s].',
                        $key,
                        implode(', ', array_keys($data))
                    ),
                    self::ERROR_ERROR_DATA_KEY_MISSING
                );
            }
        }
    }
    
    public function getLevel() : int
    {
        return (int)$this->error->level;
    }
    
    public function getCode() : int
    {
        return (int)$this->error->code;
    }
    
    public function getMessage() : string
    {
        return (string)$this->error->message;
    }
    
    public function getLine() : int
    {
        return (int)$this->error->line;
    }
    
    public function getColumn() : int
    {
        return (int)$this->error->column;
    }
    
    public function isWarning() : bool
    {
        return $this->isLevel(LIBXML_ERR_WARNING);
    }
    
    public function isError() : bool
    {
        return $this->isLevel(LIBXML_ERR_ERROR);
    }
    
    public function isFatal() : bool
    {
        return $this->isLevel(LIBXML_ERR_FATAL);
    }
    
    public function isLevel(int $level) : bool
    {
        return $this->getLevel() === $level;
    }
    
    public function isTypeTagMismatch() : bool
    {
        return $this->isCode(XMLHelper_LibXML::TAG_NAME_MISMATCH);
    }
    
    public function isTypeUnknownTag() : bool
    {
        return $this->isCode(XMLHelper_LibXML::XML_HTML_UNKNOWN_TAG);
    }
    
    public function isCode(int $code) : bool
    {
        return $this->getCode() === $code;
    }
    
    public function serialize() : string
    {
        return json_encode($this->toArray());
    }
    
    public function toArray() : array
    {
        return array(
            'code' => $this->getCode(),
            'column' => $this->getColumn(),
            'level' => $this->getLevel(),
            'message' => $this->getMessage(),
            'line' => $this->getLine()
        );
    }
}
