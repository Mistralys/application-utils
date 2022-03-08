<?php
/**
 * File containing the {@link OperationResult_Collection} class.
 *
 * @package Application Utils
 * @subpackage OperationResult
 * @see OperationResult_Collection
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Storage for several operation result instances, that acts
 * as a regular operation result. 
 * 
 * Can be used as replacement result object, which will catch 
 * all makeError() and makeSuccess() calls as separate error
 * or success instances. Adding a collection to a collection
 * will make it inherit all results the target collection contains.
 *
 * @package Application Utils
 * @subpackage OperationResult
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class OperationResult_Collection extends OperationResult
{
   /**
    * @var OperationResult[]
    */
    protected $results = array();

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeError(string $message, int $code=0) : OperationResult
    {
        return $this->add('makeError', $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeSuccess(string $message, int $code=0) : OperationResult
    {
        return $this->add('makeSuccess', $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeWarning(string $message, int $code=0) : OperationResult
    {
        return $this->add('makeWarning', $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return $this
     */
    public function makeNotice(string $message, int $code=0) : OperationResult
    {
        return $this->add('makeNotice', $message, $code);
    }

    /**
     * @param string $method
     * @param string $message
     * @param int $code
     * @return $this
     */
    protected function add(string $method, string $message, int $code=0) : OperationResult
    {
        $result = new OperationResult($this->subject);
        $result->$method($message, $code);
        
        $this->results[] = $result;
        
        return $this;
    }

    /**
     * Adds a result to the collection.
     *
     * @param OperationResult $result
     * @return $this
     */
    public function addResult(OperationResult $result) : OperationResult_Collection
    {
        if($result instanceof OperationResult_Collection)
        {
            return $this->importCollection($result);
        }

        $this->results[] = $result;
        
        return $this;
    }

    /**
     * Merges the target collection's results with this collection.
     *
     * @param OperationResult_Collection $collection
     * @return $this
     */
    private function importCollection(OperationResult_Collection $collection) : OperationResult_Collection
    {
        $results = $collection->getResults();
        
        foreach($results as $result)
        {
            $this->addResult($result);
        }
        
        return $this;
    }
    
   /**
    * @return OperationResult[]
    */
    public function getResults() : array
    {
        return $this->results;
    }
    
    public function isValid() : bool
    {
        foreach($this->results as $result)
        {
            if(!$result->isValid())
            {
                return false;
            }
        }
        
        return true;
    }
    
    public function hasCode() : bool
    {
        foreach($this->results as $result)
        {
            if($result->hasCode())
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function getCode() : int
    {
        foreach($this->results as $result)
        {
            if($result->hasCode())
            {
                return $result->getCode();
            }
        }
        
        return 0;
    }
    
    public function getMessage(string $type='') : string
    {
        foreach($this->results as $result)
        {
            $msg = $result->getMessage($type);
            
            if(!empty($msg))
            {
                return $msg;
            }
        }
        
        return '';
    }
    
    public function containsCode(int $code) : bool
    {
        foreach($this->results as $result)
        {
            if($result->getCode() === $code)
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function countErrors() : int
    {
        return $this->countByType(self::TYPE_ERROR);
    }
    
    public function countWarnings() : int
    {
        return $this->countByType(self::TYPE_WARNING);
    }
    
    public function countSuccesses() : int
    {
        return $this->countByType(self::TYPE_SUCCESS);
    }
    
    public function countNotices() : int
    {
        return $this->countByType(self::TYPE_NOTICE);
    }
    
    public function countByType(string $type) : int
    {
        $amount = 0;
        
        foreach($this->results as $result)
        {
            if($result->isType($type))
            {
                $amount++;
            }
        }
        
        return $amount;
    }
    
    public function countResults() : int
    {
        return count($this->results);
    }

    /**
     * @return OperationResult[]
     */
    public function getErrors() : array
    {
        return $this->getByType(self::TYPE_ERROR);
    }

    /**
     * @return OperationResult[]
     */
    public function getSuccesses() : array
    {
        return $this->getByType(self::TYPE_SUCCESS);
    }

    /**
     * @return OperationResult[]
     */
    public function getWarnings() : array
    {
        return $this->getByType(self::TYPE_WARNING);
    }

    /**
     * @return OperationResult[]
     */
    public function getNotices() : array
    {
        return $this->getByType(self::TYPE_NOTICE);
    }

    /**
     * @param string $type
     * @return OperationResult[]
     */
    public function getByType(string $type) : array
    {
        $results = array();
        
        foreach($this->results as $result)
        {
            if($result->isType($type))
            {
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    public function isType(string $type) : bool
    {
        foreach($this->results as $result)
        {
            if($result->isType($type))
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function getSummary() : string
    {
        $lines = array();
        
        $lines[] = 'Collection #'.$this->getID();
        $lines[] = 'Subject: '.get_class($this->subject);
        
        foreach($this->results as $result)
        {
            $lines[] = ' - '.$result->getType().' #'.$result->getCode().' "'.$result->getMessage($result->getType()).'"';
        }
        
        return implode(PHP_EOL, $lines);    
    }
}
