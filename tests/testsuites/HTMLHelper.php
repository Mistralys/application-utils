<?php

declare(strict_types=1);

use AppUtils\HTMLHelper;
use PHPUnit\Framework\TestCase;

final class HTMLHelperTest extends TestCase
{
    public function test_stripComments() : void
    {
        $tests = array(
            array(
                'label' => 'Regular comment',
                'source' => '<p>Text<!-- Some comment here with <b> tag inside! -->End</p>',
                'expected' => '<p>TextEnd</p>'
            ),
            array(
                'label' => 'Regular comment with newlines',
                'source' => '<p>Text<!--'.PHP_EOL.' Some comment here with <b> tag inside! '.PHP_EOL.'-->End</p>',
                'expected' => '<p>TextEnd</p>'
            ),
            array(
                'label' => 'Ignore conditional comments',
                'source' => '<div><!--[if IE 6]><p>You are using Internet Explorer 6.</p><![endif]--></div>',
                'expected' => '<div><!--[if IE 6]><p>You are using Internet Explorer 6.</p><![endif]--></div>'
            ),
            array(
                'label' => 'Ignore conditional comments',
                'source' => '<div><!--[if gt IE 6]><!-->This code displays on non-IE browsers and on IE 7 or higher.<!--<![endif]--></div>',
                'expected' => '<div><!--[if gt IE 6]><!-->This code displays on non-IE browsers and on IE 7 or higher.<!--<![endif]--></div>'
            )
        );

        foreach($tests as $test)
        {
            $this->assertEquals($test['expected'], HTMLHelper::stripComments($test['source']), $test['label']);
        }
    }

    public function test_inject() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'html' => '',
                'expected' => '<p>{INSERT}</p>'
            ),
            array(
                'label' => 'Single paragraph',
                'html' => '<p>Some text</p>',
                'expected' => '<p>Some text{INSERT}</p>'
            ),
            array(
                'label' => 'Several paragraphs',
                'html' => '<p>Some text</p><p>Another text</p>',
                'expected' => '<p>Some text</p><p>Another text{INSERT}</p>'
            ),
            array(
                'label' => 'Ordered list',
                'html' => '<p>Some text</p><ul><li>Another text</li></ul>',
                'expected' => '<p>Some text</p><ul><li>Another text</li></ul><p>{INSERT}</p>'
            ),
            array(
                'label' => 'Ordered list, uppercase tags',
                'html' => '<P>Some text</P><UL><LI>Another text</LI></UL>',
                'expected' => '<P>Some text</P><UL><LI>Another text</LI></UL><p>{INSERT}</p>'
            ),
            array(
                'label' => 'Duplicate content tags',
                'html' => '<p>Some text</p><p>Another text</p><p>Some text</p>',
                'expected' => '<p>Some text</p><p>Another text</p><p>Some text{INSERT}</p>'
            ),
            array(
                'label' => 'Table',
                'html' => '<table><tr><td>Some text</td></tr></table>',
                'expected' => '<table><tr><td>Some text</td></tr></table><p>{INSERT}</p>'
            )
        );

        $insert = '<a href="https://mistralys.eu">Mistralys</a>';

        foreach ($tests as $test)
        {
            $result = HTMLHelper::injectAtEnd($insert, $test['html']);
            $expected = str_replace('{INSERT}', $insert, $test['expected']);

            $this->assertEquals($expected, $result, $test['label']);
        }
    }
}
