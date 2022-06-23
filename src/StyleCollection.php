<?php
/**
 * File containing the class {@see \AppUtils\StyleCollection}.
 *
 * @see \AppUtils\StyleCollection
 *@subpackage HTML
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\StyleCollection\StyleOptions;
use AppUtils\StyleCollection\StylesRenderer;

/**
 * Utility class used to hold CSS styles, with
 * chainable methods and an easy-to-use API for handling
 * and filtering values.
 *
 * @package AppUtils
 * @subpackage HTML
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class StyleCollection implements Interface_Stringable
{
    /**
     * @var array<string,string>
     */
    private $styles = array();

    /**
     * @param array<string,string|number|NumberInfo|Interface_Stringable|NULL> $styles
     */
    public function __construct(array $styles=array())
    {
        $this->options = new StyleOptions();

        $this->setStyles($styles);
    }

    /**
     * @param array<string,string|number|NumberInfo|Interface_Stringable|NULL> $styles
     * @return StyleCollection
     */
    public static function create(array $styles=array()) : StyleCollection
    {
        return new StyleCollection($styles);
    }

    public function hasStyles() : bool
    {
        return !empty($this->styles);
    }

    /**
     * @return array<string,string>
     */
    public function getStyles() : array
    {
        return $this->styles;
    }

    // region: 1) Setting styles

    public function parseStylesString(string $string) : StyleCollection
    {
        $lines = explode(';', $string);

        foreach($lines as $line)
        {
            $parts = explode(':', $line);

            $this->style(trim($parts[0]), trim($parts[1]));
        }

        return $this;
    }

    /**
     * Adds an associative array with styles.
     *
     * NOTE: Uses {@see StyleCollection::styleAuto()} to add
     * the individual styles.
     *
     * @param array<string,string|number|NumberInfo|Interface_Stringable|NULL> $styles
     * @return $this
     */
    public function setStyles(array $styles) : StyleCollection
    {
        foreach($styles as $name => $value)
        {
            $this->styleAuto($name, $value);
        }

        return $this;
    }

    /**
     * Sets a style value.
     *
     * @param string $name
     * @param string $value
     * @param bool $important
     * @return $this
     */
    public function style(string $name, string $value, bool $important=false) : StyleCollection
    {
        if($value === '')
        {
            return $this;
        }

        if($important && stripos($value, '!important') === false)
        {
            $value .= ' !important';
        }

        $this->styles[$name] = $value;

        return $this;
    }

    /**
     * Adds a style, automatically detecting the value type.
     *
     * @param string $name
     * @param string|number|NumberInfo|Interface_Stringable|NULL $value
     * @param bool $important
     * @return $this
     */
    public function styleAuto(string $name, $value, bool $important=false) : StyleCollection
    {
        if($value instanceof NumberInfo)
        {
            return $this->style($name, $value->toCSS(), $important);
        }

        return $this->style($name, (string)$value, $important);
    }

    public function stylePX(string $name, int $px, bool $important=false) : StyleCollection
    {
        return $this->style($name, $px.'px', $important);
    }

    public function stylePercent(string $name, float $percent, bool $important=false) : StyleCollection
    {
        return $this->style($name, $percent.'%', $important);
    }

    public function styleEM(string $name, float $em, bool $important=false) : StyleCollection
    {
        return $this->style($name, $em.'em', $important);
    }

    public function styleREM(string $name, float $em, bool $important=false) : StyleCollection
    {
        return $this->style($name, $em.'rem', $important);
    }

    /**
     * Adds a number, using the {@see parseNumber()} function
     * to parse the value, and convert it to CSS.
     *
     * @param string $name
     * @param NumberInfo|string|int|float|NULL $value
     * @param bool $important
     * @return $this
     */
    public function styleParseNumber(string $name, $value, bool $important=false) : StyleCollection
    {
        return $this->styleNumber($name, parseNumber($value), $important);
    }

    /**
     * Adds an existing number info instance to add its CSS value.
     *
     * @param string $name
     * @param NumberInfo $info
     * @param bool $important
     * @return $this
     */
    public function styleNumber(string $name, NumberInfo $info, bool $important=false) : StyleCollection
    {
        $this->style($name, $info->toCSS(), $important);
        return $this;
    }

    public function remove(string $name) : StyleCollection
    {
        if(isset($this->styles[$name]))
        {
            unset($this->styles[$name]);
        }

        return $this;
    }

    /**
     * Merges all styles of this collection with those
     * from the specified collection, overwriting existing
     * styles.
     *
     * @param StyleCollection $collection
     * @return $this
     */
    public function mergeWith(StyleCollection $collection) : StyleCollection
    {
        return $this->setStyles($collection->getStyles());
    }

    /**
     * Merges all the specified style collections into
     * a new collection that contains all styles from
     * all collections.
     *
     * NOTE: Order is important, since every merge can
     * overwrite existing styles.
     *
     * @param StyleCollection ...$collections
     * @return StyleCollection
     */
    public static function merge(...$collections) : StyleCollection
    {
        $all = self::create();

        foreach($collections as $collection)
        {
            $all->mergeWith($collection);
        }

        return $all;
    }

    // endregion

    // region: 3) Rendering

    public function render() : string
    {
        return (new StylesRenderer($this))->render();
    }

    public function display() : StyleCollection
    {
        echo $this->render();

        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }

    // endregion

    // region: 2) Options handling

    /**
     * @var StyleOptions
     */
    private $options;

    /**
     * Use the option collection to customize the way
     * the styles are rendered.
     *
     * @return StyleOptions
     */
    public function getOptions() : StyleOptions
    {
        return $this->options;
    }

    /**
     * Switches the rendering to stylesheet mode: This
     * will automatically format the styles for use in
     * a CSS stylesheet (newlines, indenting, etc.).
     *
     * @return $this
     */
    public function configureForStylesheet() : StyleCollection
    {
        $this->options->configureForStylesheet();
        return $this;
    }

    /**
     * Switches the rendering to inline mode: compact list of
     * styles for use in HTML attributes for example.
     *
     * (This is the default configuration)
     *
     * @return StyleCollection
     */
    public function configureForInline() : StyleCollection
    {
        $this->options->configureForInline();
        return $this;
    }

    // endregion
}
