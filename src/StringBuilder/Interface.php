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
interface StringBuilder_Interface extends Interface_Stringable
{
    /**
     * Renders the string builder to a string.
     * 
     * @return string
     */
     function render() : string;
     
    /**
     * Renders the string and echos it.
     */
     function display() : void;
}
