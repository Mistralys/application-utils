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
   /**
    * @var callable
    */
    private $callback;
    
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }
    
    public function isExcluded(string $paramName, $paramValue): bool
    {
        return call_user_func($this->callback, $paramName, $paramValue) === true;
    }
}
