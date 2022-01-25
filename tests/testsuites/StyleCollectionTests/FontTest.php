<?php

declare(strict_types=1);

namespace StyleCollectionTests;

use AppUtils\StyleCollection\StyleBuilder;
use PHPUnit\Framework\TestCase;

class FontTest extends TestCase
{
    public function test_fontFamilyFallback() : void
    {
        $this->assertEquals(
            'font-family:Arial, serif',
            (string)StyleBuilder::create()
                ->font()->family()->arial()->fallback()->serif()
        );
    }

    public function test_fontFamilyImportant() : void
    {
        $this->assertEquals(
            'font-family:Arial, serif !important',
            (string)StyleBuilder::create()
                ->font()->family()->arial()->fallback()->serif(true)
        );
    }

    public function test_fontFamilyAutoQuotes() : void
    {
        $this->assertEquals(
            'font-family:\'Trebuchet MS\', Verdana, serif',
            (string)StyleBuilder::create()
                ->font()
                ->family()
                ->trebuchetMS()
                ->verdana()
                ->fallback()
                ->serif()
        );
    }

    public function test_fontFamilyCustom() : void
    {
        $this->assertEquals(
            'font-family:\'Custom Font\', serif',
            (string)StyleBuilder::create()
                ->font()
                ->family()
                ->custom('Custom Font')
                ->fallback()
                ->serif()
        );
    }

    public function test_fontSizes() : void
    {
        $this->assertEquals(
            'font-size:42bananas',
            (string)StyleBuilder::create()
                ->font()->size()->custom('42bananas')
        );

        $this->assertEquals(
            'font-size:50%',
            (string)StyleBuilder::create()
                ->font()->size()->percent(50)
        );

        $this->assertEquals(
            'font-size:4.2em',
            (string)StyleBuilder::create()
                ->font()->size()->em(4.2)
        );

        $this->assertEquals(
            'font-size:4.2rem',
            (string)StyleBuilder::create()
                ->font()->size()->rem(4.2)
        );

        $this->assertEquals(
            'font-size:42px',
            (string)StyleBuilder::create()
                ->font()->size()->px(42)
        );
    }

    public function test_fontSizeRelative() : void
    {
        $this->assertEquals(
            'font-size:smaller',
            (string)StyleBuilder::create()
                ->font()->size()->relativeSmaller()
        );

        $this->assertEquals(
            'font-size:larger',
            (string)StyleBuilder::create()
                ->font()->size()->relativeLarger()
        );
    }

    public function test_combineStyles() : void
    {
        $this->assertEquals(
            'font-family:Arial, serif;font-size:14px;font-style:italic;font-weight:700',
            (string)StyleBuilder::create()
                ->font()->family()->arial()->fallback()->serif()
                ->font()->style()->italic()
                ->font()->weight()->bold()
                ->font()->size()->px(14)
        );
    }

}
