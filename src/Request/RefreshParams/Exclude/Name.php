<?php
/**
 * File containing the {@link Request_RefreshParams_Exclude_Name} class.
 * @package Application Utils
 * @subpackage Request
 * @see Request_RefreshParams_Exclude_Name
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Excludes a request parameter by exact name match.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_RefreshParams_Exclude_Name extends Request_RefreshParams_Exclude
{
   /**
    * @var string
    */
    private $name;
    
    public function __construct(string $paramName)
    {
        $this->name = $paramName;
    }
    
    public function isExcluded(string $paramName, $paramValue): bool
    {
        return $paramName === $this->name;
    }
}
