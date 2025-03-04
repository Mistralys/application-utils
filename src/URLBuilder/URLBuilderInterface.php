<?php
/**
 * @package Application Utils
 * @subpackage URL Builder
 */

declare(strict_types=1);

namespace AppUtils\URLBuilder;

use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\URLInfo;

/**
 * Interface for URL builder instances.
 * See {@see URLBuilder} for the implementation.
 *
 * @package Application Utils
 * @subpackage URL Builder
 * @see URLBuilder
 */
interface URLBuilderInterface extends RenderableInterface
{
    /**
     * Removes a parameter if it exists.
     * @param string $name
     * @return $this
     */
    public function remove(string $name) : self;

    /**
     * Inherits a parameter value from the current
     * request if it exists.
     *
     * @param string $name
     * @return $this
     */
    public function inheritParam(string $name) : self;

    /**
     * Imports an array of parameter values.
     * @param array<string,string|int|float|bool|null> $params
     * @return $this
     */
    public function import(array $params) : self;

    /**
     * Imports the dispatcher and parameters from an application-internal URL string.
     *
     * NOTE: The host must match the current application host.
     *
     * @param string $url
     * @return $this
     */
    public function importURL(string $url) : self;

    /**
     * Imports an existing {@see URLInfo} instance to
     * populate the URL components with.
     *
     * @param URLInfo $info
     * @return self
     */
    public function importURLInfo(URLInfo $info) : self;

    /**
     * Adds a parameter, automatically determining its type.
     *
     * @param string $name
     * @param string|int|float|bool|null $value
     * @return $this
     */
    public function auto(string $name, $value) : self;

    /**
     * @param string $name
     * @param int $value
     * @return $this
     */
    public function int(string $name, int $value) : self;

    /**
     * @param string $name
     * @param float $value
     * @return $this
     */
    public function float(string $name, float $value) : self;

    /**
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function string(string $name, ?string $value) : self;

    /**
     * @param string $name
     * @param bool $value
     * @param bool $yesNo
     * @return $this
     */
    public function bool(string $name, bool $value, bool $yesNo=false) : self;

    /**
     * Adds an array as a JSON string.
     * @param string $name
     * @param array<int|string,string|int|float|bool|NULL|array> $data
     * @return $this
     * @throws JSONConverterException
     */
    public function arrayJSON(string $name, array $data) : self;

    /**
     * Sets the name of the dispatcher script to use in the URL.
     * @param string $dispatcher
     * @return $this
     */
    public function dispatcher(string $dispatcher) : self;

    /**
     * @return string The generated URL with all parameters.
     */
    public function get() : string;

    /**
     * @return array<string,string>
     */
    public function getParams() : array;

    /**
     * Gets a parameter value if it exists in the URL.
     *
     * @param string $name
     * @return string|int|float|bool|NULL|array
     */
    public function getParam(string $name);

    /**
     * Checks if a parameter exists in the URL,
     * and has a non-empty value.
     *
     * This means that it must not be `null` or
     * an empty string (since it will not be added
     * to the resulting URL string anyway).
     *
     * @param string $name
     * @return bool
     */
    public function hasParam(string $name) : bool;
}
