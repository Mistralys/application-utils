<?php

declare(strict_types=1);

namespace testsuites\ConvertHelper;

use AppUtils\ConvertHelper;
use TestClasses\BaseTestCase;

final class WordSplitterTest extends BaseTestCase
{
    public function test_default() : void
    {
        $result = ConvertHelper::string2words('Some text here, with a comma...')
            ->split();

        $this->assertSame(
            array(
                'Some',
                'text',
                'here',
                'with',
                'a',
                'comma'
            ),
            $result
        );
    }

    public function test_removeDuplicates() : void
    {
        $result = ConvertHelper::string2words('Some text here, some text here.')
            ->setRemoveDuplicates()
            ->split();

        $this->assertSame(
            array(
                'Some',
                'text',
                'here',
                'some'
            ),
            $result
        );
    }

    public function test_removeDuplicatesCaseInsensitive() : void
    {
        $result = ConvertHelper::string2words('Some text here, some text here.')
            ->setRemoveDuplicates(true, true)
            ->split();

        $this->assertSame(
            array(
                'Some',
                'text',
                'here'
            ),
            $result
        );
    }

    public function test_sorting() : void
    {
        $result = ConvertHelper::string2words('Some text here.')
            ->setSorting()
            ->split();

        $this->assertSame(
            array(
                'here',
                'Some',
                'text',
            ),
            $result
        );
    }

    public function test_minWordLength() : void
    {
        $result = ConvertHelper::string2words('The brown box jumped')
            ->setMinWordLength(4)
            ->split();

        $this->assertSame(
            array(
                'brown',
                'jumped'
            ),
            $result
        );
    }

    public function test_combined() : void
    {
        $result = ConvertHelper::string2words('Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
            ->setRemoveDuplicates(true, true)
            ->setSorting()
            ->setMinWordLength(5)
            ->split();

        $this->assertSame(
            array(
                'adipiscing',
                'consectetur',
                'dolor',
                'ipsum',
                'Lorem'
            ),
            $result
        );
    }
}

