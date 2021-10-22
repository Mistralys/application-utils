<?php
/**
 * File containing the {@link OperationResult} class.
 *
 * @package Application Utils
 * @subpackage OperationResult
 * @see OperationResult
 */

declare(strict_types=1);

namespace AppUtils;

use Throwable;

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
 * @subpackage OperationResult
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class OperationResult
{
    const TYPE_NOTICE = 'notice';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_SUCCESS = 'success';
    
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
    * @var string
    */
    protected $type = '';
    
   /**
    * @var integer
    */
    private static $counter = 0;
    
   /**
    * @var int
    */
    private $id;
    
   /**
    * The subject being validated.
    * 
    * @param object $subject
    */
    public function __construct(object $subject)
    {
        $this->subject = $subject;
        
        self::$counter++;
        
        $this->id = self::$counter;
    }
    
   /**
    * Retrieves the ID of the result, which is unique within a request.
    * 
    * @return int
    */
    public function getID() : int
    {
        return $this->id;
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
    
    public function isError() : bool
    {
        return $this->isType(self::TYPE_ERROR);
    }
    
    public function isWarning() : bool
    {
        return $this->isType(self::TYPE_WARNING);
    }
    
    public function isNotice() : bool
    {
        return $this->isType(self::TYPE_NOTICE);
    }
    
    public function isSuccess() : bool
    {
        return $this->isType(self::TYPE_SUCCESS);
    }
    
    public function isType(string $type) : bool
    {
        return $this->type === $type;
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
    * Makes the result a success, with the specified message.
    * 
    * @param string $message Should not contain a date, just the system specific info.
    * @return $this
    */
    public function makeSuccess(string $message, int $code=0) : OperationResult
    {
        return $this->setMessage(self::TYPE_SUCCESS, $message, $code, true);
    }
    
   /**
    * Sets the result as an error.
    * 
    * @param string $message Should be as detailed as possible.
    * @return $this
    */
    public function makeError(string $message, int $code=0) : OperationResult
    {
        return $this->setMessage(self::TYPE_ERROR, $message, $code, false);
    }

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeNotice(string $message, int $code) : OperationResult
    {
        return $this->setMessage(self::TYPE_NOTICE, $message, $code, true);
    }

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeWarning(string $message, int $code) : OperationResult
    {
        return $this->setMessage(self::TYPE_WARNING, $message, $code, true);
    }

    /**
     * @param string $type
     * @param string $message
     * @param int $code
     * @param bool $valid
     * @return $this
     */
    protected function setMessage(string $type, string $message, int $code, bool $valid) : OperationResult
    {
        $this->type = $type;
        $this->valid = $valid;
        $this->message = $message;
        $this->code = $code;
        
        return $this;
    }
    
    public function getType() : string
    {
        return $this->type;
    }
    
   /**
    * Retrieves the error message, if an error occurred.
    * 
    * @return string The error message, or an empty string if no error occurred.
    */
    public function getErrorMessage() : string
    {
        return $this->getMessage(self::TYPE_ERROR);
    }
    
   /**
    * Retrieves the success message, if one has been provided.
    * 
    * @return string
    */
    public function getSuccessMessage() : string
    {
        return $this->getMessage(self::TYPE_SUCCESS);
    }
    
    public function getNoticeMessage() : string
    {
        return $this->getMessage(self::TYPE_NOTICE);
    }
 
    public function getWarningMessage() : string
    {
        return $this->getMessage(self::TYPE_WARNING);
    }
    
   /**
    * Whether a specific error/success code has been specified.
    * 
    * @return bool
    */
    public function hasCode() : bool
    {
        return $this->code > 0;
    }
    
   /**
    * Retrieves the error/success code, if any. 
    * 
    * @return int The error code, or 0 if none.
    */
    public function getCode() : int
    {
        return $this->code;
    }
    
    public function getMessage(string $type='') : string
    {
        if(!empty($type))
        {
            if($this->type === $type)
            {
                return $this->message;
            }
            
            return '';
        }
        
        return $this->message;
    }

    /**
     * Marks the result as an error using the exception
     * to automatically create the error message.
     *
     * @param Throwable $e
     * @param int $code Optional code to use instead of inheriting the exception's code.
     * @param bool $withDeveloperInfo Whether to add exception developer information to the error message.
     * @return $this
     *
     * @see ConvertHelper_ThrowableInfo::renderErrorMessage()
     *
     * @throws ConvertHelper_Exception
     */
    public function makeException(Throwable $e, int $code=0, bool $withDeveloperInfo=false) : OperationResult
    {
        $info = parseThrowable($e);

        if($code === 0)
        {
            $code = $info->getCode();
        }

        return $this->makeError(
            $info->renderErrorMessage($withDeveloperInfo),
            $code
        );
    }
}
