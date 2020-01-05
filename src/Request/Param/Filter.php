<?php
/**
 * File containing the {@link Request_Param_Filter} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Filter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Base class skeleton for request parameter value filter types.
 * 
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Request_Param_Filter implements Interface_Optionable
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
    
    public function filter($value)
    {
        $this->value = $value;
        
        return $this->_filter();
    }

    abstract protected function _filter();
}
