<?php

declare(strict_types=1);

use AppUtils\HTMLTag;
use PHPUnit\Framework\TestCase;

final class HTMLTagTest extends TestCase
{
    public function test_contentAndAttributes() : void
    {
        $this->assertEquals(
            '<a href="https://testdomain.test">label</a>',
            HTMLTag::create('a')
                ->attr('href', 'https://testdomain.test')
                ->content('label')
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
                ->append('content')
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
            '<br/>',
            (string)HTMLTag::create('br')
                ->setSelfClosing()
        );
    }
}
