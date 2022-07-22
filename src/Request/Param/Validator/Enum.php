<?php
/**
 * File containing the {@link Request_Param_Validator_Enum} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Enum
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates the value according to a list of possible values.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Enum extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array(
            'values' => array()
        );
    }

    /**
     * @return mixed|NULL
     */
    protected function _validate()
    {
        if(in_array($this->value, $this->getArrayOption('values'), true)) {
            return $this->value;
        }
        
        return null;
    }
}
