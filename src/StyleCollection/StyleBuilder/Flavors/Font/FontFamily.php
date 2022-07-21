<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors\Font;

use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class FontFamily extends StyleContainer
{
    public const FONT_ARIAL = 'Arial';
    public const FONT_VERDANA = 'Verdana';
    public const FONT_HELVETICA = 'Helvetica';
    public const FONT_TAHOMA = 'Tahoma';
    public const FONT_TREBUCHET = 'Trebuchet MS';
    public const FONT_TIMES_NEW_ROMAN = 'Times New Roman';
    public const FONT_GEORGIA = 'Georgia';
    public const FONT_GARAMOND = 'Garamond';
    public const FONT_COURIER_NEW = 'Courier New';
    public const FONT_BRUSH_SCRIPT = 'Brush Script MT';

    protected function getName() : string
    {
        return 'font-family';
    }

    /**
     * @var string[]
     */
    private array $fonts = array();

    private function addFont(string $name) : FontFamily
    {
        if(!in_array($name, $this->fonts, true))
        {
            $this->fonts[] = $name;
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFonts() : array
    {
        return $this->fonts;
    }

    public function fallback() : FontFallback
    {
        return new FontFallback($this->styles, $this->collection, $this->fonts);
    }

    public function custom(string $name) : FontFamily
    {
        return $this->addFont($name);
    }

    public function arial() : FontFamily
    {
        return $this->addFont(self::FONT_ARIAL);
    }

    public function verdana() : FontFamily
    {
        return $this->addFont(self::FONT_VERDANA);
    }

    public function helvetica() : FontFamily
    {
        return $this->addFont(self::FONT_HELVETICA);
    }

    public function tahoma() : FontFamily
    {
        return $this->addFont(self::FONT_TAHOMA);
    }

    public function trebuchetMS() : FontFamily
    {
        return $this->addFont(self::FONT_TREBUCHET);
    }

    public function timesNewRoman() : FontFamily
    {
        return $this->addFont(self::FONT_TIMES_NEW_ROMAN);
    }

    public function georgia() : FontFamily
    {
        return $this->addFont(self::FONT_GEORGIA);
    }

    public function garamond() : FontFamily
    {
        return $this->addFont(self::FONT_GARAMOND);
    }

    public function courierNew() : FontFamily
    {
        return $this->addFont(self::FONT_COURIER_NEW);
    }

    public function brushScript() : FontFamily
    {
        return $this->addFont(self::FONT_BRUSH_SCRIPT);
    }
}
