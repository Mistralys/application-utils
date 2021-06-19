<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use PHPUnit\Framework\TestCase;

final class URLFinderTest extends TestCase
{
    public function test_findURLs() : void
    {
        $tests = array(
            array(
                'label' => 'Simple URL in text',
                'text' =>
                    'This is a text with a link: http://www.argh.de.',
                'expected' => array(
                    'http://www.argh.de'
                )
            ),
            array(
                'label' => 'Complex URL',
                'text' =>
                    'This is a text with http://user:pass@domain.co.uk/path/script?query=value#fragment a link.',
                'expected' => array(
                    'http://user:pass@domain.co.uk/path/script?query=value#fragment'
                )
            ),
            array(
                'label' => 'In HTML tag and outside',
                'text' =>
                    '<a href="http://domain.is">http://foo.bar.de/path</a>',
                'expected' => array(
                    'http://domain.is',
                    'http://foo.bar.de/path'
                )
            ),
            array(
                'label' => 'Separated with commas or the like',
                'text' =>
                    'http://domain.is,http://foo.bar.de/path;https://somewhere.com',
                'expected' => array(
                    'http://domain.is',
                    'http://foo.bar.de/path',
                    'https://somewhere.com'
                )
            ),
            array(
                'label' => 'In HTML tag and outside',
                'text' =>
                    '<a href="http://domain.is">http://foo.bar.de/path</a>',
                'expected' => array(
                    'http://domain.is',
                    'http://foo.bar.de/path'
                )
            ),
            array(
                'label' => 'Duplicate URLs',
                'text' =>
                    'http://domain.is http://domain.is http://domain.is',
                'expected' => array(
                    'http://domain.is'
                )
            ),
            array(
                'label' => 'Duplicate URLs, different cases',
                'text' =>
                    'http://domain.is HTTP://domain.is http://DOMAIN.IS',
                'expected' => array(
                    'http://domain.is',
                    'HTTP://domain.is',
                    'http://DOMAIN.IS'
                )
            ),
            array(
                'label' => 'URLs without scheme',
                'text' =>
                    'domain.is otherdomain.de?param=two',
                'expected' => array(
                    'domain.is',
                    'otherdomain.de?param=two'
                )
            ),
            array(
                'label' => 'Phone links',
                'text' => 'Call us at tel:8771216543 or tel://646554565651, and international: tel:+564564545111. US offices: tel:+1(800)555-1212.',
                'omit-mailto' => true,
                'expected' => array(
                    'tel:8771216543',
                    'tel:646554565651', // scheme is corrected by default.
                    'tel:+564564545111',
                    'tel:+1(800)555-1212'
                )
            ),
            array(
                'label' => 'URL with URL encoded redirect link',
                'text' =>
                    'Click here: https://redirect.to?url='.urlencode('https://target.com#jumpmark').' to redirect.',
                'expected' => array(
                    'https://redirect.to?url='.urlencode('https://target.com#jumpmark')
                )
            )
        );

        foreach($tests as $test)
        {
            $result = ConvertHelper::createURLFinder($test['text'])
                ->getURLs();

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_findEmails() : void
    {
        $tests = array(
            array(
                'label' => 'Complex URL',
                'text' =>
                    'This is a text with http://user:pass@domain.co.uk/path/script?query=value#fragment a link.',
                'omit-mailto' => false,
                'expected' => array(
                    'http://user:pass@domain.co.uk/path/script?query=value#fragment',
                )
            ),
            array(
                'label' => 'Email addresses',
                'text' =>
                    'mailto:someone@foo.com mailto:first.last@sub.domain.com without@mailto.com',
                'omit-mailto' => false,
                'expected' => array(
                    'mailto:someone@foo.com',
                    'mailto:first.last@sub.domain.com',
                    'mailto:without@mailto.com'
                )
            ),
            array(
                'label' => 'Email addresses',
                'text' => 'mailto:someone@foo.com mailto:first.last@sub.domain.com without@mailto.com',
                'omit-mailto' => true,
                'expected' => array(
                    'someone@foo.com',
                    'first.last@sub.domain.com',
                    'without@mailto.com'
                )
            ),
            array(
                'label' => 'Email addresses, mixed case',
                'text' => 'SOMEONE@foo.com first.last@SUB.DOMAIN.COM',
                'omit-mailto' => true,
                'expected' => array(
                    'someone@foo.com',
                    'first.last@sub.domain.com'
                )
            )
        );

        foreach($tests as $test)
        {
            $result = ConvertHelper::createURLFinder($test['text'])
                ->includeEmails()
                ->omitMailto($test['omit-mailto'])
                ->getURLs();

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_sorting() : void
    {
        $text = 'https://domain.com http://www.argh.de http://foo.com';

        $result = ConvertHelper::createURLFinder($text)->enableSorting()->getURLs();

        $expected = array(
            'http://foo.com',
            'http://www.argh.de',
            'https://domain.com',
        );

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_params() : void
    {
        $text = 'https://domain.com?z=1&b=2&a=3 https://domain.com?a=3&z=1&b=2';

        $result = ConvertHelper::createURLFinder($text)
            ->enableNormalizing()
            ->getURLs();

        $expected = array(
            'https://domain.com?a=3&b=2&z=1'
        );

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_caseInsensitive() : void
    {
        $text = 'https://domain.com HTTPS://domain.com https://DOMAIN.COM';

        $result = ConvertHelper::createURLFinder($text)
            ->enableNormalizing()
            ->getURLs();

        $expected = array(
            'https://domain.com'
        );

        $this->assertEquals($expected, $result);
    }
}
