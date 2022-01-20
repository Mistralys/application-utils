<?php

declare(strict_types=1);

use AppUtils\StyleCollection;
use AppUtils\StyleCollection\StyleBuilder;
use PHPUnit\Framework\TestCase;
use function AppUtils\parseNumber;

class StyleCollectionTest extends TestCase
{
    public function test_defaultSettings() : void
    {
        $this->assertEquals(
            'display:block;white-space:nowrap',
            StyleCollection::create()
                ->style('display', 'block')
                ->style('white-space', 'nowrap')
                ->render()
        );
    }

    public function test_spaceBeforeValue() : void
    {
        $collection = StyleCollection::create()
            ->style('display', 'block')
            ->style('white-space', 'nowrap');

        $collection->getOptions()->enableSpaceBeforeValue();

        $this->assertEquals(
            'display: block;white-space: nowrap',
            $collection->render()
        );
    }

    public function test_newline() : void
    {
        $collection = StyleCollection::create()
            ->style('display', 'block')
            ->style('white-space', 'nowrap');

        $collection->getOptions()->enableNewline();

        $expected = <<<EOT
display:block;
white-space:nowrap
EOT;

        $this->assertEquals(
            $expected,
            $collection->render()
        );
    }

    public function test_numberInfo() : void
    {
        $this->assertEquals(
            'width:42px',
            StyleCollection::create()
                ->styleNumber('width', parseNumber(42))
                ->render()
        );

        $this->assertEquals(
            'width:42%',
            StyleCollection::create()
                ->styleNumber('width', parseNumber('42%'))
                ->render()
        );

        $this->assertEquals(
            'width:42em',
            StyleCollection::create()
                ->styleNumber('width', parseNumber('42em'))
                ->render()
        );
    }

    public function test_important() : void
    {
        $this->assertEquals(
            'width:42px !important',
            StyleCollection::create()
                ->style('width', '42px !important')
                ->render()
        );

        $this->assertEquals(
            'width:42px !important',
            StyleCollection::create()
                ->style('width', '42px', true)
                ->render()
        );
    }

    public function test_indentLevel() : void
    {
        $expected = <<<EOT
    display: block;
    white-space: nowrap;
EOT;

        $this->assertEquals(
            $expected,
            StyleCollection::create()
                ->style('display', 'block')
                ->style('white-space', 'nowrap')
                ->configureForStylesheet()
                ->render()
        );
    }

    public function test_sorting() : void
    {
        $this->assertEquals(
            'a:a;b:b;c:c',
            StyleCollection::create()
            ->style('c', 'c')
            ->style('a', 'a')
            ->style('b', 'b')
            ->render()
        );
    }

    public function test_display() : void
    {
        $this->assertEquals(
            'display:block',
            (string)StyleBuilder::create()
                ->display()->block()
        );
    }

    public function test_width() : void
    {
        $this->assertEquals(
            'width:auto',
            (string)StyleBuilder::create()
                ->width()->auto()
        );
    }
}
