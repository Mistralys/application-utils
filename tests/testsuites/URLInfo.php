<?php

use PHPUnit\Framework\TestCase;

use AppUtils\URLInfo;

final class URLInfoTest extends TestCase
{
    public function test_parsing()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'valid' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Whitespace string',
                'url' => '       ',
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Whitespace string with newlines',
                'url' => "    \n    \r   \t    ",
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Random non-URL string',
                'url' => 'Foo and bar jump over the fox',
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'HTML tag',
                'url' => '<foo>bar</foo>',
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Regular URL',
                'url' => 'http://www.foo.com',
                'valid' => true,
                'normalized' => 'http://www.foo.com'
            ),
            array(
                'label' => 'URL with whitespace',
                'url' => '   http://www.foo.com    ',
                'valid' => true,
                'normalized' => 'http://www.foo.com'
            ),
            array(
                'label' => 'URL with newlines',
                'url' => "  \n http://www.\rfoo.com  \r  ",
                'valid' => true,
                'normalized' => 'http://www.foo.com'
            ),
            array(
                'label' => 'URL with the weird hyphen',
                'url' => "http://www.foo-bar.com",
                'valid' => true,
                'normalized' => 'http://www.foo-bar.com'
            ),
            array(
                'label' => 'With whitespaces within the URL',
                'url' => "http://www.   foo-bar.   com /  some/ folder /",
                'valid' => true,
                'normalized' => 'http://www.foo-bar.com/some/folder/'
            )
        );
        
        foreach($tests as $test)
        {
            $info = new URLInfo($test['url']);
            
            $this->assertEquals($test['valid'], $info->isValid(), $test['label']);
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label']);
        }
    }
    
    public function test_detectEmail()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'isEmail' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Simple email address, without mailto',
                'url' => 'foo@bar.com',
                'isEmail' => true,
                'normalized' => 'mailto:foo@bar.com',
            ),
            array(
                'label' => 'Simple email address, with mailto',
                'url' => 'mailto:foo@bar.com',
                'isEmail' => true,
                'normalized' => 'mailto:foo@bar.com',
            ),
            array(
                'label' => 'With whitespace',
                'url' => '    mailto:      foo@  bar.com   ',
                'isEmail' => true,
                'normalized' => 'mailto:foo@bar.com',
            ),
            array(
                'label' => 'With different characters',
                'url' => 'foo_bar-test/hey+crazy!@some-bar.co.uk',
                'isEmail' => true,
                'normalized' => 'mailto:foo_bar-test/hey+crazy!@some-bar.co.uk',
            )
        );
        
        foreach($tests as $test)
        {
            $info = new URLInfo($test['url']);
            
            $this->assertEquals($test['isEmail'], $info->isEmail(), $test['label'].' Error: '.$info->getErrorMessage());
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label'].' Error: '.$info->getErrorMessage());
        }
    }
    
    public function test_detectFragment()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'isFragment' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Regular fragment',
                'url' => '#foo',
                'isFragment' => true,
                'normalized' => '#foo',
            ),
            array(
                'label' => 'With whitespace',
                'url' => '    #foo    ',
                'isFragment' => true,
                'normalized' => '#foo',
            ),
            array(
                'label' => 'With newlines and tabs',
                'url' => "  \n  #foo  \r    \t ",
                'isFragment' => true,
                'normalized' => '#foo',
            ),
            array(
                'label' => 'Not a fragment',
                'url' => 'http://www.foo.com#foo',
                'isFragment' => false,
                'normalized' => 'http://www.foo.com#foo',
            ),
            array(
                'label' => 'With just some letters before it',
                'url' => "some text bar#foo",
                'isFragment' => true,
                'normalized' => '#foo',
            )
        );
        
        foreach($tests as $test)
        {
            $info = new URLInfo($test['url']);
            
            $this->assertEquals($test['isFragment'], $info->isAnchor(), $test['label'].' Error: '.$info->getErrorMessage());
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label'].' Error: '.$info->getErrorMessage());
        }
    }
    
    public function test_detectPhone()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'isPhone' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Phone with +',
                'url' => 'tel://+33 123456789',
                'isPhone' => true,
                'normalized' => 'tel://+33123456789',
            ),
            array(
                'label' => 'Phone with 00',
                'url' => 'tel://0033 12 34 56 78',
                'isPhone' => true,
                'normalized' => 'tel://003312345678',
            ),
            array(
                'label' => 'Free spacing',
                'url' => 'tel://    +  33 12 34 56 78',
                'isPhone' => true,
                'normalized' => 'tel://+3312345678',
            ),
            array(
                'label' => 'With newlines and tabs',
                'url' => "tel://  \n  +  \r 33 12 34 \t 56 78",
                'isPhone' => true,
                'normalized' => 'tel://+3312345678',
            )
        );
        
        foreach($tests as $test)
        {
            $info = new URLInfo($test['url']);
            
            $this->assertEquals($test['isPhone'], $info->isPhoneNumber(), $test['label'].' Error: '.$info->getErrorMessage());
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label'].' Error: '.$info->getErrorMessage());
        }
    }
}
