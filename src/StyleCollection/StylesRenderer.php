<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection;

use AppUtils\StyleCollection;

class StylesRenderer
{
    /**
     * @var StyleCollection
     */
    private $collection;

    /**
     * @var StyleOptions
     */
    private $options;

    public function __construct(StyleCollection $collection)
    {
        $this->collection = $collection;
        $this->options = $collection->getOptions();
    }

    public function render() : string
    {
        if($this->collection->hasStyles())
        {
            return implode(
                $this->resolveSeparator(),
                $this->renderLines()
            ).$this->resolveSuffix();
        }

        return '';
    }

    private function resolveSuffix() : string
    {
        if($this->options->isTrailingSemicolonEnabled())
        {
            return ';';
        }

        return '';
    }

    private function resolveSeparator() : string
    {
        if($this->options->isNewlineEnabled())
        {
            return ';'.PHP_EOL;
        }

        return ';';
    }

    private function resolveIndent() : string
    {
        $indentLevel = $this->options->getIndentLevel();

        if($indentLevel > 0)
        {
            return str_repeat($this->options->getIndentChar(), $indentLevel);
        }

        return '';
    }

    private function resolveValueSpace() : string
    {
        if($this->options->isSpaceBeforeValueEnabled())
        {
            return ' ';
        }

        return '';
    }

    private function renderLines() : array
    {
        $lines = array();
        $styles = $this->collection->getStyles();
        $indent = $this->resolveIndent();
        $valueSpace = $this->resolveValueSpace();

        if($this->options->isSortingEnabled())
        {
            ksort($styles);
        }

        foreach($styles as $name => $value)
        {
            $lines[] = sprintf(
                '%s%s:%s%s',
                $indent,
                $name,
                $valueSpace,
                $value
            );
        }

        return $lines;
    }
}
