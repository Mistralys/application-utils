<?php

declare(strict_types=1);

namespace StyleCollectionTests;

use AppUtils\StyleCollection\StyleBuilder;
use PHPUnit\Framework\TestCase;

class SizeTest extends TestCase
{
    public function test_width() : void
    {
        $this->assertEquals(
            'width:auto',
            (string)StyleBuilder::create()
                ->width()->auto()
        );

        $this->assertEquals(
            'width:42.6% !important',
            (string)StyleBuilder::create()
                ->width()->percent(42.6, true)
        );

        $this->assertEquals(
            'width:1.1em',
            (string)StyleBuilder::create()
                ->width()->em(1.1)
        );
    }

    public function test_height() : void
    {
        $this->assertEquals(
            'height:auto',
            (string)StyleBuilder::create()
                ->height()->auto()
        );

        $this->assertEquals(
            'height:42.6% !important',
            (string)StyleBuilder::create()
                ->height()->percent(42.6, true)
        );

        $this->assertEquals(
            'height:1.1em',
            (string)StyleBuilder::create()
                ->height()->em(1.1)
        );
    }
}
