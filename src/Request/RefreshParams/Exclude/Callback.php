<?php
/**
 * File containing the {@link Request_RefreshParams_Exclude_Callback} class.
 * @package Application Utils
 * @subpackage Request
 * @see Request_RefreshParams_Exclude_Callback
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Excludes a request parameter by matching its name to 
 * a callback function.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_RefreshParams_Exclude_Callback extends Request_RefreshParams_Exclude
{
    const ERROR_INVALID_CALLBACK = 62101;
    
   /**
    * @var callable
    */
    private $callback;
    
    public function __construct($callback)
    {
        if(!is_callable($callback))
        {
            throw new Request_Exception(
                'Invalid exclusion callback',
                sprintf(
                    'The variable [%s] is not a valid callback.',
                    parseVariable($callback)->enableType()->toString()
                ),
                self::ERROR_INVALID_CALLBACK
            );
        }
        
        $this->callback = $callback;
    }
    
    public function isExcluded(string $paramName, $paramValue): bool
    {
        return call_user_func($this->callback, $paramName, $paramValue) === true;
    }
}
