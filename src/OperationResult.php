<?php
/**
 * File containing the {@link OperationResult} class.
 *
 * @package Application Utils
 * @subpackage Core
 * @see OperationResult
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Operation result container: can be used to store 
 * details on the results of an operation, and its
 * status. It is intended to be used if the operation 
 * failing is not critical (not worthy of an exception).
 * 
 * For example, this can be used as return value of
 * a validation operation, or any other process that
 * can return error information.
 *
 * @package Application Utils
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class OperationResult
{
   /**
    * @var string
    */
    protected $message = '';
    
   /**
    * @var bool
    */
    protected $valid = true;
  
   /**
    * @var object
    */
    protected $subject;
    
   /**
    * @var integer
    */
    protected $code = 0;
    
   /**
    * The subject being validated.
    * 
    * @param object $subject
    */
    public function __construct(object $subject)
    {
        $this->subject = $subject;
    }
    
   /**
    * Whether the validation was successful.
    * 
    * @return bool
    */
    public function isValid() : bool
    {
        return $this->valid;
    }
    
   /**
    * Retrieves the subject that was validated.
    * 
    * @return object
    */
    public function getSubject() : object
    {
        return $this->subject;
    }
    
   /**
    * Makes the result a succes, with the specified message.
    * 
    * @param string $message Should not contain a date, just the system specific info.
    * @return OperationResult
    */
    public function makeSuccess(string $message, int $code=0) : OperationResult
    {
        return $this->setMessage($message, $code, true);
    }
    
   /**
    * Sets the result as an error.
    * 
    * @param string $message Should be as detailed as possible.
    * @return OperationResult
    */
    public function makeError(string $message, int $code=0) : OperationResult
    {
        return $this->setMessage($message, $code, false);
    }
    
    protected function setMessage(string $message, int $code, bool $valid) : OperationResult
    {
        $this->valid = $valid;
        $this->message = $message;
        $this->code = $code;
        
        return $this;
    }
    
   /**
    * Retrieves the error message, if an error occurred.
    * 
    * @return string The error message, or an empty string if no error occurred.
    */
    public function getErrorMessage() : string
    {
        return $this->getMessage(false);
    }
    
   /**
    * Retrieves the success message, if one has been provided.
    * 
    * @return string
    */
    public function getSuccessMessage() : string
    {
        return $this->getMessage(true);
    }
    
    public function hasCode() : bool
    {
        return $this->code > 0;
    }
    
    public function getCode() : int
    {
        return $this->code;
    }
    
    protected function getMessage(bool $valid) : string
    {
        if($this->valid === $valid) 
        {
            return $this->message;
        }
        
        return '';
    }
}
