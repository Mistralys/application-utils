<?php
/**
 * File containing the {@link Request_Param_Validator} class.
 * 
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\Request\RequestParam;

/**
 * Base class skeleton for request parameter validation types.
 * 
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Request_Param_Validator implements Interface_Optionable
{
    use Traits_Optionable;
    
    /**
     * @var RequestParam
     */
    protected $param;
    
   /**
    * @var mixed
    */
    protected $value;
    
   /**
    * @var bool
    */
    protected $isSubvalue = false;
    
    public function __construct(RequestParam $param, bool $subval)
    {
        $this->param = $param;
        $this->isSubvalue = $subval;
    }
    
    public function validate($value)
    {
        $this->value = $value;
        
        return $this->_validate();
    }
    
    abstract protected function _validate();
}
