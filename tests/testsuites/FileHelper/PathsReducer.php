<?php

use AppUtils\FileHelper;
use PHPUnit\Framework\TestCase;

final class FileHelper_PathsReducerTest extends TestCase
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
