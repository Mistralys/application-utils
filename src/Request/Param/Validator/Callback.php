<?php
/**
 * File containing the {@link Request_Param_Validator_Callback} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Callback
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates a string containing only letters, lowercase and uppercase, and numbers.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Callback extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array(
            'arguments' => array(),
            'callback' => null
        );
    }

    /**
     * @return mixed|NULL
     */
    protected function _validate()
    {
        $args = $this->getArrayOption('arguments');
        array_unshift($args, $this->value);
        
        $result = call_user_func_array($this->getOption('callback'), $args);
        if($result !== false) {
            return $this->value;
        }
        
        return null;
    }
}
