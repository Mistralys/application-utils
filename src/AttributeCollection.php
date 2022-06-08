<?php
/**
 * File containing the class {@see \AppUtils\AttributeCollection}.
 *
 * @package AppUtils
 * @subpackage HTML
 * @see \AppUtils\AttributeCollection
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\AttributeCollection\AttributesRenderer;
use AppUtils\AttributeCollection\Filtering;
use AppUtils\Interfaces\StylableInterface;
use AppUtils\Traits\StylableTrait;

/**
 * Utility class used to hold HTML attributes, with
 * chainable methods and an easy-to-use API for handling
 * and filtering values.
 *
 * @package AppUtils
 * @subpackage HTML
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AttributeCollection
    implements
    Interface_Stringable,
    Interface_Classable,
    StylableInterface
{
    use Traits_Classable;
    use StylableTrait;

    /**
     * @var array<string,string>
     */
    private array $attributes = array();

    /**
     * @var StyleCollection
     */
    public StyleCollection $styles;

    private ?AttributesRenderer $renderer = null;

    /**
     * @param array<string,string|number|bool|NULL|Interface_Stringable|StringBuilder_Interface> $attributes
     */
    private function __construct(array $attributes)
    {
        $this->styles = StyleCollection::create();

        $this->setAttributes($attributes);
    }

    public function getStyles() : StyleCollection
    {
        return $this->styles;
    }

    /**
     * @param array<string,string|number|bool|NULL|Interface_Stringable|StringBuilder_Interface|NULL> $attributes
     * @return $this
     */
    public function setAttributes(array $attributes) : AttributeCollection
    {
        foreach($attributes as $name => $value)
        {
            $this->attr($name, $value);
        }

        return $this;
    }

    public function getAttribute(string $name, string $default='') : string
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @param array<string,string|number|bool|NULL|Interface_Stringable|StringBuilder_Interface> $attributes
     * @return AttributeCollection
     */
    public static function create(array $attributes=array()) : AttributeCollection
    {
        return new AttributeCollection($attributes);
    }

    public function prop(string $name, bool $enabled=true) : AttributeCollection
    {
        if($enabled)
        {
            return $this->attr($name, $name);
        }

        return $this->remove($name);
    }

    /**
     * @param string $name
     * @param string|number|bool|Interface_Stringable|StringBuilder_Interface|NULL $value
     * @return $this
     */
    public function attr(string $name, $value) : AttributeCollection
    {
        $string = Filtering::value2string($value);

        if($name === 'class')
        {
            return $this->addClasses(ConvertHelper::explodeTrim(' ', $string));
        }

        if($name === 'style')
        {
            $this->styles->parseStylesString($string);
            return $this;
        }

        if($string !== '')
        {
            $this->attributes[$name] = $string;
        }

        return $this;
    }

    /**
     * Adds an attribute, and escapes double quotes in the value.
     *
     * @param string $name
     * @param string|number|bool|Interface_Stringable|StringBuilder_Interface|NULL $value $value
     * @return $this
     */
    public function attrQuotes(string $name, $value) : AttributeCollection
    {
        $this->attr($name, $value);

        if(isset($this->attributes[$name]))
        {
            $this->attributes[$name] = Filtering::quotes($this->attributes[$name]);
        }

        return $this;
    }

    public function attrURL(string $name, string $url) : AttributeCollection
    {
        return $this->attr($name, Filtering::URL($url));
    }

    public function remove(string $name) : AttributeCollection
    {
        if(isset($this->attributes[$name]))
        {
            unset($this->attributes[$name]);
        }

        return $this;
    }

    public function hasAttribute(string $name) : bool
    {
        return isset($this->attributes[$name]);
    }

    public function hasAttributes() : bool
    {
        $attributes = $this->getAttributes();

        return !empty($attributes);
    }

    private function getRenderer() : AttributesRenderer
    {
        if(isset($this->renderer))
        {
            return $this->renderer;
        }

        $renderer = new AttributesRenderer($this);
        $this->renderer = $renderer;
        return $renderer;
    }

    /**
     * Retrieves the attributes as an associative array
     * with name => value pairs.
     *
     * @return array<string,string>
     */
    public function getAttributes() : array
    {
        return $this->getRenderer()->compileAttributes();
    }

    /**
     * Like {@see AttributeCollection::getAttributes()}, but
     * without the dynamically generated attributes (like
     * `class` and `style`). These are just the attributes
     * that have been set manually.
     *
     * @return array<string,string>
     */
    public function getRawAttributes() : array
    {
        return $this->attributes;
    }

    public function render() : string
    {
        return $this->getRenderer()->render();
    }

    public function __toString()
    {
        return $this->render();
    }

    // region: Flavors

    public function name(string $name) : AttributeCollection
    {
        return $this->attr('name', $name);
    }

    public function id(string $id) : AttributeCollection
    {
        return $this->attr('id', $id);
    }

    public function href(string $url) : AttributeCollection
    {
        return $this->attrURL('href', $url);
    }

    public const TARGET_BLANK = '_blank';

    public function target(string $value=self::TARGET_BLANK) : AttributeCollection
    {
        return $this->attr('target', $value);
    }

    // endregion
}
