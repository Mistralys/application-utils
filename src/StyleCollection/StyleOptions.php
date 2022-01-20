<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection;

use AppUtils\Interface_Optionable;
use AppUtils\StyleCollection;
use AppUtils\Traits_Optionable;

class StyleOptions implements Interface_Optionable
{
    use Traits_Optionable;

    public const OPTION_INDENT_LEVEL = 'indent';
    public const OPTION_NEWLINES = 'newlines';
    public const OPTION_INDENT_CHAR = 'indent-char';
    public const OPTION_SPACE_BEFORE_VALUE = 'space-before-value';
    public const OPTION_TRAILING_SEMICOLON = 'trailing-semicolon';
    public const OPTION_SORTING = 'sorting';

    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_INDENT_LEVEL => 0,
            self::OPTION_INDENT_CHAR => '    ',
            self::OPTION_NEWLINES => false,
            self::OPTION_SPACE_BEFORE_VALUE => false,
            self::OPTION_TRAILING_SEMICOLON => false,
            self::OPTION_SORTING => true
        );
    }

    public function enableSorting(bool $enabled=true) : StyleOptions
    {
        return $this->setOption(self::OPTION_SORTING, $enabled);
    }

    public function enableSpaceBeforeValue(bool $enabled=true) : StyleOptions
    {
        return $this->setOption(self::OPTION_SPACE_BEFORE_VALUE, $enabled);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function enableNewline(bool $enabled=true) : StyleOptions
    {
        return $this->setOption(self::OPTION_NEWLINES, $enabled);
    }

    /**
     * Sets the indentation level to use to indent
     * the styles.
     *
     * NOTE: Enabling this automatically enables newlines.
     *
     * @param int $level `0` = Off. For each level above `0`, prepends the indent character to each style.
     * @return StyleOptions
     */
    public function setIndentLevel(int $level) : StyleOptions
    {
        return $this->setOption(self::OPTION_INDENT_LEVEL, $level);
    }

    /**
     * Sets the character/string to use for indentation.
     *
     * Default is to use 4 spaces. To use tabs, there is
     * the method {@see StyleCollection::setIndentCharTab()}
     *
     * @param string $char
     * @return StyleOptions
     */
    public function setIndentChar(string $char) : StyleOptions
    {
        return $this->setOption(self::OPTION_INDENT_CHAR, $char);
    }

    public function setIndentCharTab() : StyleOptions
    {
        return $this->setIndentChar("\t");
    }

    public function isSpaceBeforeValueEnabled() : bool
    {
        return $this->getBoolOption(self::OPTION_SPACE_BEFORE_VALUE);
    }

    public function isSortingEnabled() : bool
    {
        return $this->getBoolOption(self::OPTION_SORTING);
    }

    public function isIndentEnabled() : bool
    {
        return $this->getIndentLevel() > 0;
    }

    public function isNewlineEnabled() : bool
    {
        return $this->isIndentEnabled() || $this->getBoolOption(self::OPTION_NEWLINES);
    }

    public function isTrailingSemicolonEnabled() : bool
    {
        return $this->getBoolOption(self::OPTION_TRAILING_SEMICOLON);
    }

    public function getIndentLevel() : int
    {
        return $this->getIntOption(self::OPTION_INDENT_LEVEL);
    }

    public function getIndentChar() : string
    {
        return $this->getStringOption(self::OPTION_INDENT_CHAR);
    }

    public function enableTrailingSemicolon(bool $enabled=true) : StyleOptions
    {
        return $this->setOption(self::OPTION_TRAILING_SEMICOLON, $enabled);
    }

    public function configureForStylesheet() : StyleOptions
    {
        return $this
            ->enableSpaceBeforeValue()
            ->enableNewline()
            ->enableTrailingSemicolon()
            ->setIndentLevel(1);
    }

    public function configureForInline() : StyleOptions
    {
        return $this
            ->enableSpaceBeforeValue(false)
            ->enableNewline(false)
            ->enableTrailingSemicolon(false)
            ->setIndentLevel(0);
    }
}
