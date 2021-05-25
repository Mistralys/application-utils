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
}