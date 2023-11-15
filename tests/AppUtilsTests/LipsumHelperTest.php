<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\LipsumHelper;
use TestClasses\BaseTestCase;

final class LipsumHelperTest extends BaseTestCase
{
    public function test_containsLipsum() : void
    {
        $detector = LipsumHelper::containsLipsum('asqad');

        $this->assertFalse($detector->isDetected());
    }

    public function test_containsLipsumFound() : void
    {
        $detector = LipsumHelper::containsLipsum('Lorem ipsum');

        $this->assertNotEmpty($detector->getDetectedWords());
        $this->assertSame(2, $detector->countDetectedWords());
        $this->assertTrue($detector->isDetected());
    }

    public function test_minWordsFound() : void
    {
        $detector = LipsumHelper::containsLipsum('Lorem ipsum dolor sit amet')
            ->setMinWords(4);

        $this->assertNotEmpty($detector->getDetectedWords());
        $this->assertTrue($detector->isDetected());
    }

    public function test_minWordsNotFound() : void
    {
        $detector = LipsumHelper::containsLipsum('Lorem ipsum with some other text')
            ->setMinWords(4);

        $this->assertNotEmpty($detector->getDetectedWords());
        $this->assertFalse($detector->isDetected());
    }
}
