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

use AppUtils\Interfaces\StringableInterface;
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
    public const TYPE_NOTICE = 'notice';
    public const TYPE_WARNING = 'warning';
    public const TYPE_ERROR = 'error';
    public const TYPE_SUCCESS = 'success';
    
    protected string $message = '';
    protected object $subject;
    protected int $code = 0;
    protected string $type = '';
    private static int $counter = 0;
    private int $id;
    private int $count = 1;
    private string $label;

    /**
    * The subject being validated.
    * 
    * @param object $subject
    * @param string|StringableInterface|NULL $label An optional human-readable label of the operation.
    */
    public function __construct(object $subject, $label=null)
    {
        self::$counter++;
        
        $this->id = self::$counter;
        $this->subject = $subject;

        $this->setLabel($label);
    }

    /**
     * The operation's human-readable label, if specified.
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @param string|StringableInterface|NULL $label
     * @return $this
     */
    public function setLabel($label) : self
    {
        $this->label = (string)$label;
        return $this;
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
     * A hash of the message, used to identify duplicate messages.
     * @return string
     */
    public function getHash() : string
    {
        return md5(serialize(array(
            $this->code,
            $this->type,
            $this->message
        )));
    }
    
   /**
    * Whether the validation was successful.
    * 
    * @return bool
    */
    public function isValid() : bool
    {
        return $this->type !== self::TYPE_ERROR;
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
    * @param string $message Should not contain a date, just the system-specific info.
    * @return $this
    */
    public function makeSuccess(string $message, int $code=0) : OperationResult
    {
        return $this->setMessage(self::TYPE_SUCCESS, $message, $code);
    }
    
   /**
    * Sets the result as an error.
    * 
    * @param string $message Should be as detailed as possible.
    * @return $this
    */
    public function makeError(string $message, int $code=0) : OperationResult
    {
        return $this->setMessage(self::TYPE_ERROR, $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeNotice(string $message, int $code=0) : OperationResult
    {
        return $this->setMessage(self::TYPE_NOTICE, $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeWarning(string $message, int $code) : OperationResult
    {
        return $this->setMessage(self::TYPE_WARNING, $message, $code);
    }

    /**
     * @param string $type
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function setMessage(string $type, string $message, int $code) : OperationResult
    {
        $this->type = $type;
        $this->message = $message;
        $this->code = $code;
        
        return $this;
    }

    /**
     * The message type.
     *
     * @return string Can be empty if no message has been added.
     *
     * @see self::TYPE_NOTICE
     * @see self::TYPE_WARNING
     * @see self::TYPE_SUCCESS
     * @see self::TYPE_ERROR
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Human-readable label of the message type.
     * @return string
     */
    public function getTypeLabel() : string
    {
        return self::getTypeLabels()[$this->type] ?? t('Message');
    }

    /**
     * @var array<string,string>|null
     */
    private static ?array $typeLabels = null;

    /**
     * @return array<string,string>
     */
    public static function getTypeLabels() : array
    {
        if(!isset(self::$typeLabels)) {
            self::$typeLabels = array(
                self::TYPE_NOTICE => t('Notice'),
                self::TYPE_WARNING => t('Warning'),
                self::TYPE_ERROR => t('Error'),
                self::TYPE_SUCCESS => t('Success')
            );
        }

        return self::$typeLabels;
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
    * Retrieves the success message if one has been provided.
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

    /**
     * The amount of times this message was triggered,
     * in case it was triggered multiple times.
     *
     * @return int
     */
    public function getCount() : int
    {
        return $this->count;
    }

    /**
     * Increases the internal counter of the number of times
     * this message has been triggered.
     *
     * NOTE: This is used by the {@see OperationResult_Collection}
     * to keep track of the number of times a message was triggered.
     * Use {@see self::getCount()} to retrieve the count.
     *
     * @return $this
     */
    public function increaseCount() : self
    {
        $this->count++;
        return $this;
    }

    /**
     * Gets the result message if any was set.
     *
     * @param string $type Optional type to filter the message by.
     * @return string An empty string if no message was set.
     */
    public function getMessage(string $type='') : string
    {
        if(!empty($type)) {
            if($this->type === $type) {
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
     * @see ThrowableInfo::renderErrorMessage()
     *
     * @throws ConvertHelper_Exception
     */
    public function makeException(Throwable $e, int $code=0, bool $withDeveloperInfo=false) : OperationResult
    {
        $info = parseThrowable($e);

        if($code === 0) {
            $code = $info->getCode();
        }

        return $this->makeError(
            $info->renderErrorMessage($withDeveloperInfo),
            $code
        );
    }
}
