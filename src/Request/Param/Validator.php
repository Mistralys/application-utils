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
     * @var Request_Param
     */
    protected $param;
    
    protected $value;
    
    public function __construct(Request_Param $param)
    {
        $this->param = $param;
    }
    
    public function validate($value)
    {
        $this->value = $value;
        
        return $this->_validate();
    }
    
    abstract protected function _validate();
}
