<?php

declare(strict_types=1);

use AppUtils\HTMLTag;
use AppUtils\HTMLTag\CannedTags;
use PHPUnit\Framework\TestCase;

final class HTMLTagTest extends TestCase
{
    protected function setUp() : void
    {
        HTMLTag::getGlobalOptions()->setSelfCloseStyle(HTMLTag::SELF_CLOSE_STYLE_NONE);
    }

    public function test_contentAndAttributes() : void
    {
        $this->assertEquals(
            '<a href="https://testdomain.test">label</a>',
            HTMLTag::create('a')
                ->href('https://testdomain.test')
                ->setContent('label')
                ->render()
        );
    }

    public function test_attributesOnly() : void
    {
        $this->assertEquals(
            '<code class="test"></code>',
            (string)HTMLTag::create('code')
                ->addClass('test')
        );
    }

    public function test_contentOnly() : void
    {
        $this->assertEquals(
            '<code>content</code>',
            (string)HTMLTag::create('code')
                ->addText('content')
        );
    }

    /**
     * By default, empty tags are not allowed when the attributes
     * are also empty.
     */
    public function test_empty() : void
    {
        $this->assertEquals('', (string)HTMLTag::create('code'));
    }

    /**
     * Explicitly allow empty tags.
     */
    public function test_emptyAllowed() : void
    {
        $this->assertEquals(
            '<code></code>',
            (string)HTMLTag::create('code')
                ->setEmptyAllowed()
        );
    }

    public function test_selfClosing() : void
    {
        $this->assertEquals(
            '<br>',
            (string)HTMLTag::create('br')
                ->setSelfClosing()
        );

        HTMLTag::getGlobalOptions()->setSelfCloseSlash();

        $this->assertEquals(
            '<br/>',
            (string)HTMLTag::create('br')
                ->setSelfClosing()
        );
    }

    public function test_cannedTags() : void
    {
        $tests = array(
            array(
                'tag' => CannedTags::anchor('/path/to/page', 'label'),
                'expected' => '<a href="/path/to/page">label</a>'
            ),
            array(
                'tag' => CannedTags::anchor('/path/to/page'),
                'expected' => '<a href="/path/to/page"></a>'
            ),
            array(
                'tag' => CannedTags::br(),
                'expected' => '<br>'
            ),
            array(
                'tag' => CannedTags::div('content'),
                'expected' => '<div>content</div>'
            ),
            array(
                'tag' => CannedTags::p('Text'),
                'expected' => '<p>Text</p>'
            )
        );

        foreach($tests as $test)
        {
            $this->assertEquals(
                (string)$test['tag'],
                $test['expected']
            );
        }
    }

    public function test_keepEmptyAttribute() : void
    {
        $this->assertEquals(
            '<option value="">Content</option>',
            (string)HTMLTag::create('option')
                ->attr('value', '', true)
                ->setContent('Content')
        );
    }
}
