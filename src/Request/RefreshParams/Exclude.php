<?php
/**
 * File containing the {@link Request_RefreshParams_Exclude} class.
 * @package Application Utils
 * @subpackage Request
 * @see Request_RefreshParams_Exclude
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Abstract base class for a way to specify a parameter 
 * that should be excluded from the list.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Request_RefreshParams_Exclude
{
    /**
     * @param string $paramName
     * @param mixed|NULL $paramValue
     * @return bool
     */
    abstract public function isExcluded(string $paramName, $paramValue) : bool;
}
