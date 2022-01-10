<?php
/**
 * File containing the class {@see \AppUtils\HTMLTag}.
 *
 * @package AppUtils
 * @subpackage HTML
 * @see \AppUtils\HTMLTag
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\HTMLTag\GlobalOptions;

/**
 * Helper class for generating individual HTML tags,
 * with chainable methods.
 *
 * @package AppUtils
 * @subpackage HTML
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://github.com/Mistralys/application-utils/wiki/HTMLTag
 */
class HTMLTag implements Interface_Stringable, Interface_Classable
{
    public const SELF_CLOSE_STYLE_SLASH = 'slash';
    public const SELF_CLOSE_STYLE_NONE = 'none';

    /**
     * @var AttributeCollection
     */
    public $attributes;

    /**
     * @var string
     */
    private $name;

    /**
     * @var StringBuilder
     */
    public $content;

    /**
     * @var bool
     */
    private $selfClosing = false;

    /**
     * @var bool
     */
    private $allowEmpty = false;

    /**
     * @var GlobalOptions|NULL
     */
    private static $globalOptions;

    private function __construct(string $name, AttributeCollection $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->content = sb();
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function setSelfClosing(bool $selfClosing=true) : HTMLTag
    {
        $this->selfClosing = $selfClosing;
        return $this;
    }

    public function isSelfClosing() : bool
    {
        return $this->selfClosing;
    }

    public function setEmptyAllowed(bool $allowed=true) : HTMLTag
    {
        $this->allowEmpty = $allowed;
        return $this;
    }

    public function isEmptyAllowed() : bool
    {
        if($this->isSelfClosing())
        {
            return true;
        }

        return $this->allowEmpty;
    }

    public static function create(string $name, ?AttributeCollection $attributes=null) : HTMLTag
    {
        if($attributes === null)
        {
            $attributes = AttributeCollection::create();
        }

        return new HTMLTag($name, $attributes);
    }

    public function hasAttributes() : bool
    {
        return $this->attributes->hasAttributes();
    }

    /**
     * Returns true if the tag has no content, and no attributes.
     * By default, an empty tag is not rendered.
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return !$this->hasAttributes() && $this->renderContent() === '';
    }

    public function render() : string
    {
        if(!$this->isEmptyAllowed() && $this->isEmpty())
        {
            return '';
        }

        return
            $this->renderOpen().
            $this->renderContent().
            $this->renderClose();
    }

    public static function getGlobalOptions() : GlobalOptions
    {
        if(!isset(self::$globalOptions))
        {
            self::$globalOptions = new GlobalOptions();
        }

        return self::$globalOptions;
    }

    public function getSelfClosingChar() : string
    {
        if($this->selfClosing && self::getGlobalOptions()->getSelfCloseStyle() === self::SELF_CLOSE_STYLE_SLASH)
        {
            return '/';
        }

        return '';
    }

    public function renderOpen() : string
    {
        return sprintf(
            '<%s%s%s>',
            $this->name,
            $this->attributes,
            $this->getSelfClosingChar()
        );
    }

    public function renderClose() : string
    {
        if($this->selfClosing)
        {
            return '';
        }

        return sprintf('</%s>', $this->name);
    }

    /**
     * Adds a bit of text to the content (with an automatic space at the end).
     *
     * @param string|number|StringBuilder_Interface|NULL $content
     * @return $this
     */
    public function addText($content) : HTMLTag
    {
        $this->content->add($content);
        return $this;
    }

    /**
     * Adds a bit of HTML at the end of the content.
     *
     * @param string|number|StringBuilder_Interface|NULL $content
     * @return $this
     */
    public function addHTML($content) : HTMLTag
    {
        $this->content->html($content);
        return $this;
    }

    public function setContent($content) : HTMLTag
    {
        $this->content = sb()->add($content);
        return $this;
    }

    public function renderContent() : string
    {
        if($this->selfClosing)
        {
            return '';
        }

        return (string)$this->content;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function attr(string $name, string $value) : HTMLTag
    {
        $this->attributes->attr($name, $value);
        return $this;
    }

    public function prop(string $name, bool $enabled=true) : HTMLTag
    {
        $this->attributes->prop($name, $enabled);
        return $this;
    }

    // region: Flavors

    public function name(string $name) : HTMLTag
    {
        $this->attributes->name($name);
        return $this;
    }

    public function id(string $id) : HTMLTag
    {
        $this->attributes->id($id);
        return $this;
    }

    public function href(string $url) : HTMLTag
    {
        $this->attributes->href($url);
        return $this;
    }

    public function src(string $url) : HTMLTag
    {
        $this->attributes->attrURL('src', $url);
        return $this;
    }

    // endregion

    // region: Classable interface

    public function addClass(string $name) : HTMLTag
    {
        $this->attributes->addClass($name);
        return $this;
    }

    public function addClasses(array $names) : HTMLTag
    {
        $this->attributes->addClasses($names);
        return $this;
    }

    public function hasClass(string $name) : bool
    {
        return $this->attributes->hasClass($name);
    }

    public function removeClass(string $name) : HTMLTag
    {
        $this->attributes->removeClass($name);
        return $this;
    }

    public function getClasses() : array
    {
        return $this->attributes->getClasses();
    }

    public function classesToString() : string
    {
        return $this->attributes->classesToString();
    }

    public function classesToAttribute() : string
    {
        return $this->attributes->classesToAttribute();
    }

    public function hasClasses() : bool
    {
        return $this->attributes->hasClasses();
    }

    // endregion
}
