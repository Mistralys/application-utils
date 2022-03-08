<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors\Font;

use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class FontStyle extends StyleContainer
{
    public const STYLE_ITALIC = 'italic';
    public const STYLE_OBLIQUE = 'oblique';
    public const OBLIQUE_DEFAULT_DEGREES = 14;

    protected function getName() : string
    {
        return 'font-style';
    }

    public function italic(bool $important=false) : StyleBuilder
    {
        return $this->setStyle(self::STYLE_ITALIC, $important);
    }

    /**
     * font-style:oblique
     *
     * @param int $degrees -90 to 90, default 14
     * @param bool $important
     * @return StyleBuilder
     * @link https://developer.mozilla.org/fr/docs/Web/CSS/font-style
     */
    public function oblique(int $degrees=self::OBLIQUE_DEFAULT_DEGREES, bool $important=false) : StyleBuilder
    {
        $value = self::STYLE_OBLIQUE;

        if($degrees !== self::OBLIQUE_DEFAULT_DEGREES)
        {
            $value .= sprintf(
                ' %sdeg',
                $degrees
            );
        }

        return $this->setStyle($value, $important);
    }
}
