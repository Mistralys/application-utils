<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\ImageHelper;
use PHPUnit\Framework\TestCase;
use function AppUtils\RGBAColor\imgSize;

final class ImageTests extends TestCase
{
    /**
     * Ensure that the image classes and functions are available.
     */
    public function test_packageIsAvailable() : void
    {
        ImageHelper::createNew(200, 100);

        $this->addToAssertionCount(1);

        $size = imgSize(array(100,80));

        $this->addToAssertionCount(1);
    }
}
