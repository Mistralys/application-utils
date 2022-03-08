<?php

declare(strict_types=1);

namespace testsuites\FileHelperTests;

use AppUtils\FileHelper;
use PHPUnit\Framework\TestCase;

final class PathsReducerTest extends TestCase
{
    public function test_reduce(): void
    {
        $reducer = FileHelper::createPathsReducer();

        $reducer->addPath(__DIR__.'/../../../src/FileHelper/PHPClassInfo/Class.php');
        $reducer->addPath(__DIR__.'/../../../src/FileHelper/Exception.php');
        $reducer->addPath(__DIR__.'/../../../src/FileHelper/');
        $reducer->addPath(__DIR__.'/../../../src/OperationResult');

        $result = $reducer->reduce();

        $this->assertEquals(
            array(
                'FileHelper/PHPClassInfo/Class.php',
                'FileHelper/Exception.php',
                'FileHelper',
                'OperationResult'
            ),
            $result
        );
    }

    /**
     * Paths passed in the constructor must be used as well
     * as those added via methods.
     */
    public function test_constructor() : void
    {
        $reducer = FileHelper::createPathsReducer(array(
            'foo/bar',
            'foo/bar/test.html'
        ));

        $this->assertEquals(
            array(
                'bar',
                'bar/test.html'
            ),
            $reducer->reduce()
        );
    }

    public function test_reduceEmpty(): void
    {
        $reducer = FileHelper::createPathsReducer();

        $reducer->addPath(__DIR__);

        $result = $reducer->reduce();

        $this->assertEquals(
            array(
                basename(__DIR__)
            ),
            $result
        );
    }

    public function test_reduceNoPaths(): void
    {
        $reducer = FileHelper::createPathsReducer();

        $result = $reducer->reduce();

        $this->assertEquals(
            array(),
            $result
        );
    }

    public function test_reduceSinglePath(): void
    {
        $reducer = FileHelper::createPathsReducer();

        $reducer->addPath(__FILE__);

        $result = $reducer->reduce();

        $this->assertEquals(
            array(
                basename(__FILE__),
            ),
            $result
        );
    }
}
