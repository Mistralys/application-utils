<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors\Font;

use AppUtils\StyleCollection;
use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class FontFallback extends StyleContainer
{
    /**
     * @var string[]
     */
    private array $fonts;

    /**
     * @param StyleBuilder $styles
     * @param StyleCollection $collection
     * @param string[] $fonts
     */
    public function __construct(StyleBuilder $styles, StyleCollection $collection, array $fonts)
    {
        parent::__construct($styles, $collection);

        $this->fonts = $fonts;
    }

    protected function getName() : string
    {
        return 'font-family';
    }

    private function compileStyle(string $fallback, bool $important) : StyleBuilder
    {
        return $this->setStyle(implode(', ', $this->renderFonts($fallback)), $important);
    }

    /**
     * @param string $fallback
     * @return string[]
     */
    private function renderFonts(string $fallback) : array
    {
        $fonts = $this->fonts;
        $fonts[] = $fallback;
        $keep = array();

        foreach($fonts as $font)
        {
            if(strpos($font, ' ') !== false)
            {
                $font = "'".$font."'";
            }

            $keep[] = $font;
        }

        return $keep;
    }

    // region: Fallback fonts

    public const FALLBACK_SERIF = 'serif';
    public const FALLBACK_SANS_SERIF = 'sans-serif';
    public const FALLBACK_MONOSPACE = 'monospace';
    public const FALLBACK_CURSIVE = 'cursive';
    public const FALLBACK_FANTASY = 'fantasy';

    public function serif(bool $important=false) : StyleBuilder
    {
        return $this->compileStyle(self::FALLBACK_SERIF, $important);
    }

    public function sansSerif(bool $important=false) : StyleBuilder
    {
        return $this->compileStyle(self::FALLBACK_SANS_SERIF, $important);
    }

    public function cursive(bool $important=false) : StyleBuilder
    {
        return $this->compileStyle(self::FALLBACK_CURSIVE, $important);
    }

    public function monospace(bool $important=false) : StyleBuilder
    {
        return $this->compileStyle(self::FALLBACK_MONOSPACE, $important);
    }

    public function fantasy(bool $important=false) : StyleBuilder
    {
        return $this->compileStyle(self::FALLBACK_FANTASY, $important);
    }

    // endregion
}
