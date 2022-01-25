<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors\Font;

use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class FontWeight extends StyleContainer
{
    public const WEIGHT_THIN = 100;
    public const WEIGHT_EXTRA_LIGHT = 200;
    public const WEIGHT_LIGHT = 300;
    public const WEIGHT_NORMAL = 400;
    public const WEIGHT_MEDIUM = 500;
    public const WEIGHT_SEMI_BOLD = 600;
    public const WEIGHT_BOLD = 700;
    public const WEIGHT_EXTRA_BOLD = 800;
    public const WEIGHT_BLACK = 900;

    protected function getName() : string
    {
        return 'font-weight';
    }

    // region: Numeric values

    public function customNumber(int $weight, bool $important = false) : StyleBuilder
    {
        return $this->setStyle((string)$weight, $important);
    }

    /**
     * Hairline-thin - 100
     * @param bool $important
     * @return StyleBuilder
     */
    public function thin(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_THIN, $important);
    }

    /**
     * Extra-light (ultra light) - 200
     * @param bool $important
     * @return StyleBuilder
     */
    public function extraLight(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_EXTRA_LIGHT, $important);
    }

    /**
     * Light - 300
     * @param bool $important
     * @return StyleBuilder
     */
    public function light(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_LIGHT, $important);
    }

    /**
     * Normal - 400
     * @param bool $important
     * @return StyleBuilder
     */
    public function normal(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_NORMAL, $important);
    }

    /**
     * Medium - 500
     * @param bool $important
     * @return StyleBuilder
     */
    public function medium(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_MEDIUM, $important);
    }

    /**
     * Semi-bold (Demi bold) - 600
     * @param bool $important
     * @return StyleBuilder
     */
    public function semiBold(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_SEMI_BOLD, $important);
    }

    /**
     * Bold - 700
     * @param bool $important
     * @return StyleBuilder
     */
    public function bold(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_BOLD, $important);
    }

    /**
     * Extra bold (Ultra bold) - 800
     * @param bool $important
     * @return StyleBuilder
     */
    public function extraBold(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_EXTRA_BOLD, $important);
    }

    /**
     * Black (Heavy) - 900
     * @param bool $important
     * @return StyleBuilder
     */
    public function black(bool $important=false) : StyleBuilder
    {
        return $this->customNumber(self::WEIGHT_BLACK, $important);
    }

    // endregion

    // region: Keyword values

    /**
     * Bold using the `bold` keyword instead of a numeric weight (i.e. `font-weight:bold`).
     * @param bool $important
     * @return StyleBuilder
     */
    public function namedBold(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('bold', $important);
    }

    /**
     * Bold using the `normal` keyword instead of a numeric weight (i.e. `font-weight:normal`).
     * @param bool $important
     * @return StyleBuilder
     */
    public function namedNormal(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('normal', $important);
    }

    /**
     * Weight relative to the parent element using the `lighter` keyword.
     * @param bool $important
     * @return StyleBuilder
     */
    public function relativeLighter(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('lighter', $important);
    }

    /**
     * Weight relative to the parent element using the `bolder` keyword.
     * @param bool $important
     * @return StyleBuilder
     */
    public function relativeBolder(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('bolder', $important);
    }

    // endregion
}
