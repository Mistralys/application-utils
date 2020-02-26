<?php

use PHPUnit\Framework\TestCase;

use AppUtils\URLInfo;
use function AppUtils\parseURL;
use AppUtils\URLInfo_Parser;

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
            ),
            array(
                'label' => 'With HTML encoded ampersands',
                'url' => "http://www.foo-bar.com?foo=bar&amp;bar=foo&amp;lopos=yes",
                'valid' => true,
                'normalized' => 'http://www.foo-bar.com?bar=foo&foo=bar&lopos=yes'
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
    
    public function test_globalFunction()
    {
        $info = parseURL('http://foo.com');
        
        $this->assertInstanceOf(URLInfo::class, $info);
    }
    
    public function test_arrayAccess()
    {
        $info = parseURL('http://user:pass@foo.com:1234/path/to/page/index.html#fragment');        
        
        $this->assertEquals('http', $info['scheme']);
        $this->assertEquals('user', $info['user']);
        $this->assertEquals('pass', $info['pass']);
        $this->assertEquals('foo.com', $info['host']);
        $this->assertSame(1234, $info['port']);
        $this->assertEquals('/path/to/page/index.html', $info['path']);
        $this->assertEquals('fragment', $info['fragment']);
    }
    
    public function test_arrayAccess_empty()
    {
        $info = parseURL('//foo.com');
        
        $this->assertSame('', $info['scheme']);
        $this->assertSame('', $info['user']);
        $this->assertSame('', $info['pass']);
        $this->assertSame(-1, $info['port']);
        $this->assertSame('', $info['path']);
        $this->assertSame('', $info['fragment']);
    }

    public function test_scheme()
    {
        $tests = array(
            array(
                'label' => 'Regular HTTP url',
                'url' => 'http://foo.com',
                'expected' => 'http',
                'hasScheme' => true,
                'isSecure' => false,
                'isURL' => true
            ),
            array(
                'label' => 'Regular HTTPS url',
                'url' => 'https://foo.com',
                'expected' => 'https',
                'hasScheme' => true,
                'isSecure' => true,
                'isURL' => true
            ),
            array(
                'label' => 'Regular FTP url',
                'url' => 'ftp://foo.com',
                'expected' => 'ftp',
                'hasScheme' => true,
                'isSecure' => false,
                'isURL' => true
            ),
            array(
                'label' => 'Schemeless but valid URL',
                'url' => '//foo.com',
                'expected' => '',
                'hasScheme' => false,
                'isSecure' => false,
                'isURL' => true
            ),
            array(
                'label' => 'Invalid URL',
                'url' => 'foo.com',
                'expected' => '',
                'hasScheme' => false,
                'isSecure' => false,
                'isURL' => false
            )
        );

        foreach($tests as $test)
        {
            $info = parseURL($test['url']);
            
            $this->assertEquals($test['expected'], $info->getScheme(), $test['label']);
            $this->assertEquals($test['hasScheme'], $info->hasScheme(), $test['label']);
            $this->assertEquals($test['isSecure'], $info->isSecure(), $test['label']);
            $this->assertEquals($test['isURL'], $info->isURL(), $test['label']);
        }
    }
    
    public function test_port()
    {
        $tests = array(
            array(
                'label' => 'No port specified',
                'url' => 'http://foo.com',
                'expected' => -1,
                'hasPort' => false,
            ),
            array(
                'label' => 'Port specified',
                'url' => 'http://foo.com:3120',
                'expected' => 3120,
                'hasPort' => true,
            )
        );
        
        foreach($tests as $test)
        {
            $info = parseURL($test['url']);
            
            $this->assertSame($test['expected'], $info->getPort(), $test['label']);
            $this->assertEquals($test['hasPort'], $info->hasPort(), $test['label']);
        }
    }
    
   /**
    * Ensure that the same URLs, but with a different order of parameters
    * have the same hash (which is generated from the normalized URL).
    */
    public function test_getHash()
    {
        $url1 = 'http://foo.com?param1=foo&param2=bar&param3=dog';
        $url2 = 'http://foo.com?param3=dog&param1=foo&param2=bar';
        
        $info1 = parseURL($url1);
        $info2 = parseURL($url2);
        
        $this->assertEquals($info1->getNormalized(), $info2->getNormalized(), 'The normalized URLs should match.');
        $this->assertEquals($info1->getHash(), $info2->getHash(), 'The hashes should match.');
    }
    
    public function test_tryConnect()
    {
        $this->assertTrue(parseURL('https://google.com')->tryConnect(), 'Could not connect to google.com.');
        
        $this->assertFalse(parseURL('https://'.md5(microtime(true)).'.org')->tryConnect(), 'Could connect to an unknown website.');
    }
    
    public function test_normalize()
    {
        $tests = array(
            array(
                'label' => 'Regular URL',
                'value' => 'https://www.foo.com',
                'expected' => 'https://www.foo.com'
            ),
            array(
                'label' => 'With parameter',
                'value' => 'https://www.foo.com?bar=foo',
                'expected' => 'https://www.foo.com?bar=foo'
            ),
            array(
                'label' => 'With port number',
                'value' => 'https://www.foo.com:5511/path/to/page',
                'expected' => 'https://www.foo.com:5511/path/to/page'
            ),
            array(
                'label' => 'With parameter and fragment',
                'value' => 'https://www.foo.com?bar=foo#somewhere',
                'expected' => 'https://www.foo.com?bar=foo#somewhere'
            ),
            array(
                'label' => 'With path, parameter and fragment',
                'value' => 'https://www.foo.com/some/path/?bar=foo#somewhere',
                'expected' => 'https://www.foo.com/some/path/?bar=foo#somewhere'
            ),
            array(
                'label' => 'With username and password',
                'value' => 'https://username:password@www.foo.com',
                'expected' => 'https://username:password@www.foo.com'
            ),
            array(
                'label' => 'Parameter reordering',
                'value' => 'https://www.foo.com?foo=bar&bar=foo',
                'expected' => 'https://www.foo.com?bar=foo&foo=bar'
            )
        );
        
        foreach($tests as $test)
        {
            $info = parseURL($test['value']);
            
            $this->assertEquals($test['expected'], $info->getNormalized(), $test['label']);
        }
    }
    
   /**
    * The Username and password have to be URL encoded, since they
    * can contain URL-specific syntax characters. This has to be
    * handled correctly so they are URL decoded when accessing them,
    * and URL encoded when normalizing.
    * 
    * @see URLInfo_Parser::filterParsed()
    */
    public function test_credentialsSpecialCharacters()
    {
        $specialchars = 'öä§#()!?/{}';
        $encoded = urlencode($specialchars);
        
        $url = 'https://'.$encoded.':'.$encoded.'@www.foo.com';
        
        $info = parseURL($url);
        
        $this->assertEquals($specialchars, $info->getUsername(), 'Username should be URL decoded.');
        $this->assertEquals($specialchars, $info->getPassword(), 'Password should be URL decoded.');
        $this->assertEquals('https://'.$encoded.':'.$encoded.'@www.foo.com', $info->getNormalized(), 'Password and Username should be URL encoded.');
    }
    
    public function test_normalizedWithoutAuth()
    {
        $info = parseURL('http://username:password@test.com');
        
        $this->assertEquals('http://username:password@test.com', $info->getNormalized());
        $this->assertEquals('http://test.com', $info->getNormalizedWithoutAuth());
    }
    
    /**
     * Excluding parameters in URLs.
     */
    public function test_excludeParam()
    {
        $tests = array(
            array(
                'label' => 'The URL should stay unchanged.',
                'url' => 'http://test.com/feedback?medium=somevalue',
                'expected' => 'http://test.com/feedback?medium=somevalue'
            ),
            array(
                'label' => ' The ac parameter should be stripped.',
                'url' => 'http://test.com/feedback?medium=somevalue&ac=stripme',
                'expected' => 'http://test.com/feedback?medium=somevalue'
            ),
            array(
                'label' => ' The ac parameter should be stripped, other parameters left alone.',
                'url' => 'http://test.com/feedback?medium=somevalue&ac=stripme&medium2=othervalue',
                'expected' => 'http://test.com/feedback?medium=somevalue&medium2=othervalue'
            )
        );
        
        foreach($tests as $entry)
        {
            $info = parseURL($entry['url']);
            
            $info->excludeParam('ac', 'Reason');
            
            $this->assertEquals($entry['expected'], $info->getNormalized(), $entry['label']);
        }
    }
    
    /**
     * Check the switching between parameter exclusion modes.
     */
    public function test_disableParamExclusion()
    {
        $tests = array(
            array(
                'label' => ' The ac parameter should be stripped, other parameters left alone.',
                'url' => 'http://test.com/feedback?ac=stripme&medium1=somevalue&medium2=othervalue',
                'excluded' => 'http://test.com/feedback?medium1=somevalue&medium2=othervalue',
                'not-excluded' => 'http://test.com/feedback?ac=stripme&medium1=somevalue&medium2=othervalue'
            )
        );
        
        foreach($tests as $entry)
        {
            $info = parseURL($entry['url']);
            
            // the default state: no parameters excluded.
            $this->assertFalse($info->isParamExclusionEnabled(), 'By default, parameter exclusion should be turned off.');
            $this->assertEquals($entry['not-excluded'], $info->getNormalized(), 'By default, URL should still contain excluded params.');
            
            // exluding a parameter should auto-enable the exclusion mode.
            $info->excludeParam('ac', 'Reason');
            $this->assertTrue($info->isParamExclusionEnabled(), 'Parameter exclusion should be auto-enabled when adding exclude params.');
            $this->assertEquals($entry['excluded'], $info->getNormalized(), 'URL should not contain any of the excluded params.');
            
            // turning it off should return the original URL with all excluded params
            $info->setParamExclusion(false);
            $this->assertFalse($info->isParamExclusionEnabled(), 'Parameter exclusion should be disabled.');
            $this->assertEquals($entry['not-excluded'], $info->getNormalized(), 'URL should contain all of the excluded params.');
            
            // turning it on again without adding new excluded parameters
            $info->setParamExclusion(true);
            $this->assertTrue($info->isParamExclusionEnabled(), 'Parameter exclusion should be enabled.');
            $this->assertEquals($entry['excluded'], $info->getNormalized(), 'URL should not contain any of the excluded params.');
        }
    }
    
    /**
     * Ensure that highlighting excluded parameters works.
     */
    public function test_highlightExcluded()
    {
        $info = parseURL('http://test.com/feedback?ac=stripme&medium1=somevalue&medium2=othervalue');
        $info->excludeParam('ac', 'Reason');
        $info->setHighlightExcluded();
        
        $highlighted = $info->getHighlighted();
        
        $this->assertStringContainsString('stripme', $highlighted, 'Should contain the excluded parameter.');
        $this->assertStringContainsString('excluded-param', $highlighted, 'Should contain the class for excluded parameters.');
    }
    
    /**
     * Ensure that checking whether an URL contains excluded parameters works as intended.
     */
    public function test_containsExcludedParams()
    {
        $tests = array(
            array(
                'label' => 'Should contain no excluded params.',
                'url' => 'http://test.com/feedback?medium=somevalue',
                'expected' => false
            ),
            array(
                'label' => 'Should contain excluded params.',
                'url' => 'http://test.com/feedback?medium=somevalue&ac=stripme',
                'expected' => true
            )
        );
        
        foreach($tests as $entry)
        {
            $info = parseURL($entry['url']);
            
            $info->excludeParam('ac', 'Reason');
            
            $this->assertEquals($entry['expected'], $info->containsExcludedParams(), $entry['label']);
        }
    }
    
}
