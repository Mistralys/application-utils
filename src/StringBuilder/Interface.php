<?php
/**
 * File containing the {@link StringBuilder_Interface} interface.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @see StringBuilder_Interface
 */

namespace AppUtils;

/**
 * Interface for the StringBuilder class.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see StringBuilder
 */
interface StringBuilder_Interface
{
    /**
     * Renders the string builder to a string.
     * 
     * @return string
     */
     function render() : string;
     
    /**
     * Converts the string builder to a string.
     * 
     * @return string
     */
     function __toString();
     
    /**
     * Renders the string and echos it.
     */
     function display() : void;
}
