<?php
/**
 * File containing the {@see AppUtils\Interface_Optionable} interface.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see Interface_Optionable
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Interface for classes that use the optionable trait.
 * The trait itself fulfills most of the interface, but
 * it is used to guarantee internal type checks will work,
 * as well as ensure the abstract methods are implemented.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Traits_Optionable
 */
interface Interface_Optionable
{
    /**
     * Sets an option to the specified value. This can be any
     * kind of variable type, including objects, as needed.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setOption(string $name, $value) : self;

    /**
     * Retrieves an option's value.
     *
     * NOTE: Use the specialized type getters to ensure an option
     * contains the expected type (for ex. getArrayOption()).
     *
     * @param string $name
     * @param mixed $default The default value to return if the option does not exist.
     * @return mixed
     */
    public function getOption(string $name, $default=null);

    /**
     * @param string $name
     * @return array<int|string,mixed>
     */
    public function getArrayOption(string $name) : array;

    public function getIntOption(string $name, int $default=0) : int;

    public function getBoolOption(string $name, bool $default=false) : bool;

    public function getStringOption(string $name, string $default='') : string;

    /**
     * Sets a collection of options at once, from an
     * associative array.
     *
     * @param array<string,mixed> $options
     * @return $this
     */
    public function setOptions(array $options) : self;

    /**
     * Returns all options in one associative array.
     *
     * @return array<string,mixed>
     */
    public function getOptions() : array;

    /**
     * Checks whether the option's value is the one specified.
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function isOption(string $name, $value) : bool;

    /**
     * Checks whether the specified option exists - even
     * if it has a NULL value.
     *
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name) : bool;

    /**
     * Retrieves the default available options as an
     * associative array with option name => value pairs.
     *
     * @return array<string,mixed>
     */
    public function getDefaultOptions() : array;
}
