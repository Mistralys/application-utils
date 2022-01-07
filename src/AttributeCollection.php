<?php

declare(strict_types=1);

namespace AppUtils;

use Throwable;

class AttributeCollection implements Interface_Stringable, Interface_Classable
{
    use Traits_Classable;

    /**
     * @var array<string,string|number>
     */
    private $attributes = array();

    /**
     * @param array<string,string|number> $attributes
     */
    private function __construct(array $attributes)
    {
        $this->setAttributes($attributes);
    }

    /**
     * @param array<string,string|number|Interface_Stringable|StringBuilder_Interface|NULL> $attributes
     * @return $this
     */
    public function setAttributes(array $attributes) : AttributeCollection
    {
        // import existing classes
        if(isset($attributes['class']))
        {
            $this->addClasses(ConvertHelper::explodeTrim(' ', $attributes['class']));
            unset($attributes['class']);
        }

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
     * @param array<string,string|number> $attributes
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
        if($value === true)
        {
            $string = 'true';
        }
        else if($value === false)
        {
            $string = 'false';
        }
        else
        {
            $string = (string)$value;
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
            $this->attributes[$name] = $this->filterQuotes($this->attributes[$name]);
        }

        return $this;
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

    public function getAttributes() : array
    {
        $attributes = $this->attributes;

        if($this->hasClasses())
        {
            $attributes['class'] = $this->classesToString();
        }

        return $attributes;
    }

    public function render() : string
    {
        $list = array();

        $attributes = $this->getAttributes();

        if(empty($attributes))
        {
            return '';
        }

        foreach($attributes as $name => $value)
        {
            if($value === '')
            {
                continue;
            }

            $list[] = $this->renderAttribute($name, $value);
        }

        return ' '.implode(' ', $list);
    }

    private function renderAttribute(string $name, string $value) : string
    {
        if($name === $value)
        {
            return $name;
        }

        return sprintf(
            '%s="%s"',
            $name,
            $value
        );
    }

    /**
     * Escapes double quotes in an attribute value by replacing
     * them with HTML entities.
     *
     * @param string $value
     * @return string
     */
    private function filterQuotes(string $value) : string
    {
        return str_replace('"', '&quot;', $value);
    }

    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch (Throwable $e)
        {
            return '';
        }
    }

    // region: Flavors

    public function href(string $url) : AttributeCollection
    {
        // Normalize ampersands, keeping already encoded ones.
        $url = str_replace(
            array('&amp;', '&', '__AMP__'),
            array('__AMP__', '__AMP__', '&amp;'),
            $url
        );

        return $this->attr('href', $url);
    }

    public const TARGET_BLANK = '_blank';

    public function target(string $value=self::TARGET_BLANK) : AttributeCollection
    {
        return $this->attr('target', $value);
    }

    // endregion
}
