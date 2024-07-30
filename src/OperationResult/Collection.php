<?php
/**
 * @package Application Utils
 * @subpackage OperationResult
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Storage for several operation result instances which acts
 * as a regular operation result. 
 * 
 * Can be used as a replacement result object, which will catch
 * all {@see self::makeError()} and {@see self::makeSuccess()}
 * calls as separate error or success instances. Adding a collection
 * to a collection will make it inherit all results the target
 * collection contains.
 *
 * @package Application Utils
 * @subpackage OperationResult
 */
class OperationResult_Collection extends OperationResult
{
   /**
    * @var array<string,OperationResult>
    */
    protected array $results = array();

    /**
     * @var int[]
     */
    protected array $codes = array();

    /**
     * Adds an error message to the collection and returns it.
     *
     * @param string $message
     * @param int $code
     * @return OperationResult Note: This is not the collection instance.
     */
    public function makeError(string $message, int $code=0) : OperationResult
    {
        return $this->add(OperationResult::TYPE_ERROR, $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return OperationResult Note: This is not the collection instance.
     */
    public function makeSuccess(string $message, int $code=0) : OperationResult
    {
        return $this->add(OperationResult::TYPE_SUCCESS, $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return OperationResult Note: This is not the collection instance.
     */
    public function makeWarning(string $message, int $code=0) : OperationResult
    {
        return $this->add(OperationResult::TYPE_WARNING, $message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return OperationResult Note: This is not the collection instance.
     */
    public function makeNotice(string $message, int $code=0) : OperationResult
    {
        return $this->add(OperationResult::TYPE_NOTICE, $message, $code);
    }

    /**
     * @param string $type
     * @param string $message
     * @param int $code
     * @param object|null $subject
     * @return OperationResult Note: This is not the collection instance.
     */
    protected function add(string $type, string $message, int $code=0, ?object $subject=null) : OperationResult
    {
        if($subject === null) {
            $subject = $this->subject;
        }

        $result = new OperationResult($subject);
        $result->setMessage($type, $message, $code);

        $hash = $result->getHash();

        if(!isset($this->results[$hash])) {
            $this->results[$hash] = $result;
        } else {
            $this->results[$hash]->increaseCount();
        }

        $code = $result->getCode();
        if($code !== 0 && !in_array($code, $this->codes, true)) {
            $this->codes[] = $code;
        }

        return $result;
    }

    /**
     * Adds a result to the collection.
     *
     * @param OperationResult $result
     * @return $this
     */
    public function addResult(OperationResult $result) : OperationResult_Collection
    {
        if($result instanceof OperationResult_Collection) {
            return $this->importCollection($result);
        }

        $this->add(
            $result->getType(),
            $result->getMessage($result->getType()),
            $result->getCode(),
            $result->getSubject()
        );

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
        foreach($collection->getResults() as $result) {
            $this->addResult($result);
        }
        
        return $this;
    }
    
   /**
    * @return OperationResult[]
    */
    public function getResults() : array
    {
        return array_values($this->results);
    }
    
    public function isValid() : bool
    {
        foreach($this->results as $result) {
            if(!$result->isValid()) {
                return false;
            }
        }
        
        return true;
    }
    
    public function hasCode() : bool
    {
        return !empty($this->codes);
    }

    /**
     * Fetches the first code in the collection.
     *
     * NOTE: This is only so the collection can act as
     * replacement of a single {@see OperationResult}.
     * For the collection, more useful is {@see self::containsCode()}
     * and {@see self::getCodes()}.
     *
     * @return int
     *
     * @see self::containsCode()
     * @see self::getCodes()
     */
    public function getCode() : int
    {
        if(!empty($this->codes)) {
            return $this->codes[0];
        }
        
        return 0;
    }

    /**
     * Gets all message codes that have been added
     * to the collection.
     *
     * @return int[]
     *
     * @see self::containsCode()
     */
    public function getCodes() : array
    {
        sort($this->codes);

        return $this->codes;
    }
    
    public function getMessage(string $type='') : string
    {
        foreach($this->results as $result) {
            $msg = $result->getMessage($type);
            if(!empty($msg)) {
                return $msg;
            }
        }
        
        return '';
    }

    /**
     * Checks whether the collection contains a message with
     * the target code.
     *
     * @param int $code
     * @return bool
     */
    public function containsCode(int $code) : bool
    {
        return in_array($code, $this->codes, true);
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
        
        foreach($this->results as $result) {
            if ($result->isType($type)) {
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
        
        foreach($this->results as $result) {
            if($result->isType($type)) {
                $results[] = $result;
                break;
            }
        }
        
        return $results;
    }
    
    public function isType(string $type) : bool
    {
        foreach($this->results as $result) {
            if($result->isType($type)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Renders a plain text summary of all messages that were
     * added to the collection.
     *
     * @return string
     */
    public function getSummary() : string
    {
        $lines = array();
        
        $lines[] = 'Collection #'.$this->getID();
        $lines[] = 'Label: "'.$this->getLabel().'"';
        $lines[] = 'Subject: '.get_class($this->subject);
        
        foreach($this->results as $result)
        {
            $lines[] = ' - '.$result->getType().' #'.$result->getCode().' "'.$result->getMessage($result->getType()).'"';
        }
        
        return implode(PHP_EOL, $lines);    
    }

    /**
     * Renders an HTML summary of all messages that were
     * added in the collection.
     *
     * @return string
     * @throws OutputBuffering_Exception
     */
    public function getSummaryHTML() : string
    {
        $summary = sb()
            ->html('<div class="operation-results">')
            ->para(sb()
                ->bold(t('Operation results'))->code('#'.$this->getID())
                ->quote($this->getLabel())
                ->nl()
                ->add(sb()->bold('Subject:')->code(get_class($this->subject)))
            );

        if(empty($this->results))
        {
            $summary->para(sb()->bold(sb()->italic('('.t('No results added.'.')'))));
        }
        else
        {
            OutputBuffering::start();
            ?>
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th><?php echo t('Type') ?></th>
                        <th><?php echo t('Code') ?></th>
                        <th><?php echo t('Count') ?></th>
                        <th><?php echo t('Message') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($this->results as $result) { ?>
                        <tr>
                            <td><?php echo $result->getTypeLabel() ?></td>
                            <td><?php echo $result->getCode() ?></td>
                            <td><?php echo $result->getCount() ?></td>
                            <td><?php echo nl2br($result->getMessage($result->getType())) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php
            $summary->html(OutputBuffering::get());
        }

        return (string)$summary
            ->html('</div>');
    }
}
