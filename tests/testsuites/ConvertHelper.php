<?php

use PHPUnit\Framework\TestCase;

use AppUtils\ConvertHelper;

final class ConvertHelperTest extends TestCase
{
    /**
     * @see ConvertHelper::areVariablesEqual()
     */
    public function test_areVariablesEqual()
    {
        $tests = array(
            array('0', 0, true, 'String zero, numeric zero'),
            array('0', null, false, 'String zero, NULL'),
            array(null, 0, false, 'NULL, numeric zero'),
            array(false, null, false, 'FALSE, NULL'),
            array(false, '', false, 'FALSE, empty string'),
            array('1', 1, true, 'String 1, numeric 1'),
            array('112.58', 112.58, true, 'String float, numeric float'),
            array('', '', true, 'Empty string, empty string'),
            array('', null, true, 'Empty string, NULL'),
            array(null, null, true, 'NULL, NULL'),
            array('string', 'other', false, 'String, different string'),
            array('string', 'string', true, 'String, same string'),
            array(array('yo'), array('yo'), true, 'Array, same array'),
            array(array('yo'), array('no'), false, 'Array, different array'),
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::areVariablesEqual($test[0], $test[1]);
            
            $this->assertEquals($test[2], $result);
        }
    }
    
   /**
    * @see ConvertHelper::filenameRemoveExtension()
    */
    public function test_filenameRemoveExtension()
    {
        $tests = array(
            'somename.ext' => 'somename',
            '/path/to/file.txt' => 'file',
            'F:\\path\name.extension' => 'name',
            'With.Several.Dots.file' => 'With.Several.Dots',
            'noextension' => 'noextension',
            'file ending in dot.' => 'file ending in dot',
            '.ext' => ''
        );
        
        foreach($tests as $string => $expected) 
        {
            $actual = ConvertHelper::filenameRemoveExtension($string);
            
            $this->assertEquals($expected, $actual);
        }
    }
    
    /**
     * @see ConvertHelper::isStringHTML()
     */
    public function test_isStringHTML()
    {
        $tests = array(
            'Text without HTML' => false,
            'Text with < signs >' => false,
            'Text with <b>Some</b> HTML' => true,
            'Just a <br> single tag' => true,
            'Auto-closing <div/> here' => true,
            '' => false,
            '    ' => false,
            '>>>>' => false,
            '<!-- -->' => false,
            'Simple & ampersand' => false,
            'Encoded &amp; ampersand' => true
        );
        
        foreach($tests as $string => $expected)
        {
            $actual = ConvertHelper::isStringHTML($string);
            
            $this->assertEquals($expected, $actual);
        }
    }
    
    public function test_bool2string()
    {
        $tests = array(
            true => 'true',
            false => 'false',
            'true' => 'true',
            'false' => 'false',
            'yes' => 'true',
            'no' => 'false',
        );
        
        foreach($tests as $bool => $expected)
        {
            $actual = ConvertHelper::bool2string($bool);
            
            $this->assertEquals($expected, $actual);
        }
    }
    
    public function test_isStringASCII()
    {
        $tests = array(
            array('regular text', true, 'Regular text'),
            array('()?%$"46[]{}!+*', true, 'ASCII Characters'),
            array('A single ö', false, 'Special character'),
            array('', true, 'Empty string'),
            array(null, true, 'NULL'),
            array(array(), false, 'Array')
        );
        
        foreach($tests as $def)
        {
            $actual = ConvertHelper::isStringASCII($def[0]);
            
            $this->assertEquals($def[1], $actual, $def[2]);
        }
    }
    
    public function test_isBooleanString()
    {
        $tests = array(
            1 => true,
            0 => true,
            '1' => true,
            '0' => true,
            'true' => true,
            'false' => true,
            'yes' => true,
            'no' => true,
            '' => false,
            null => false,
            'bla' => false
        );
        
        foreach($tests as $value => $isBool)
        {
            $this->assertEquals(ConvertHelper::isBoolean($value), $isBool);
        }
    }
    
    public function test_string2array()
    {
        $tests = array(
            array(
                'string' => 'Hello',
                'result' => array('H', 'e', 'l', 'l', 'o')
            ),
            array(
                'string' => 'äöü',
                'result' => array('ä', 'ö', 'ü')
            ),
            array(
                'string' => "And spa\ns",
                'result' => array('A', 'n', 'd', ' ', 's', 'p', 'a', "\n", 's')
            ),
        );
        
        foreach($tests as $def)
        {
            $this->assertEquals($def['result'], ConvertHelper::string2array($def['string']));
        }
    }
    
    public function test_text_cut()
    {
        $tests = array(
            array(
                'string' => 'Here is some text to test cutting on.',
                'result' => 'Here is some tex...',
                'length' => 16,
                'char' => '...'
            ),
            array(
                'string' => 'Here is some text to test cutting on.',
                'result' => 'Here is some tex [...]',
                'length' => 16,
                'char' => ' [...]'
            ),
        );
        
        foreach($tests as $def)
        {
            $this->assertEquals(
                $def['result'], 
                ConvertHelper::text_cut($def['string'], $def['length'], $def['char'])
            );
        }
    }
    
    public function test_time2string()
    {
        $tests = array(
            array(
                'time' => 20,
                'expected' => '20 seconds'
            )
        );
        
        foreach($tests as $def)
        {
            $this->assertEquals($def['expected'], ConvertHelper::time2string($def['time']));
        }
    }
    
    public function test_isBool()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => false
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => false
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => true
            ),
            array(
                'label' => 'Numeric zero',
                'value' => 0,
                'expected' => true
            ),
            array(
                'label' => 'String one',
                'value' => '1',
                'expected' => true
            ),
            array(
                'label' => 'Numeric one',
                'value' => 1,
                'expected' => true
            ),
            array(
                'label' => 'String true',
                'value' => 'true',
                'expected' => true
            ),
            array(
                'label' => 'String yes',
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'label' => 'String false',
                'value' => 'false',
                'expected' => true
            ),
            array(
                'label' => 'String true',
                'value' => 'true',
                'expected' => true
            ),
            array(
                'label' => 'Boolean true',
                'value' => true,
                'expected' => true
            ),
            array(
                'label' => 'Boolean false',
                'value' => false,
                'expected' => true
            )
        );
        
        foreach($tests as $def)
        {
            $isBool = ConvertHelper::isBoolean($def['value']);
            
            $this->assertEquals($def['expected'], $isBool, $def['label']);
        }
    }
    
    public function test_parseQueryString()
    {
        $tests = array(
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => array()
            ),
            array(
                'label' => 'Whitespace value',
                'value' => '  ',
                'expected' => array()
            ),
            array(
                'label' => 'Single parameter',
                'value' => 'foo=bar',
                'expected' => array('foo' => 'bar')
            ),
            array(
                'label' => 'Multiple parameters',
                'value' => 'foo=bar&bar=foo&something=more',
                'expected' => array(
                    'foo' => 'bar',
                    'bar' => 'foo',
                    'something' => 'more'
                )
            ),
            array(
                'label' => 'Parameters with HTML encoded ampersand',
                'value' => 'foo=bar&amp;bar=foo',
                'expected' => array(
                    'foo' => 'bar',
                    'bar' => 'foo'
                )
            ),
            array(
                'label' => 'Parameter name with dot',
                'value' => 'foo.bar=result',
                'expected' => array(
                    'foo.bar' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name with space',
                'value' => 'foo bar=result',
                'expected' => array(
                    'foo bar' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name with space and dot',
                'value' => 'f.oo bar=result',
                'expected' => array(
                    'f.oo bar' => 'result'
                )
            ),
            array(
                // with parse_str, this would not be possible since foo.bar would be converted to foo_bar.
                'label' => 'Mixed underscores and dots (conflict test)',
                'value' => 'foo.bar=result1&foo_bar=result2',
                'expected' => array(
                    'foo.bar' => 'result1',
                    'foo_bar' => 'result2'
                )
            ),
            array(
                // with parse_str, this would not be possible since foo.bar would be converted to foo_bar.
                'label' => 'Mixed underscores and spaces (conflict test)',
                'value' => 'foo bar=result1&foo_bar=result2',
                'expected' => array(
                    'foo bar' => 'result1',
                    'foo_bar' => 'result2'
                )
            ),
            array(
                // check that the replacement mechanism does not confuse parameter names
                'label' => 'Parameter names starting like other parameter names',
                'value' => 'foo=bar&foo.bar=ditto',
                'expected' => array(
                    'foo' => 'bar',
                    'foo.bar' => 'ditto'
                )
            ),
            array(
                'label' => 'Parameter name with colon',
                'value' => 'foo:bar=result',
                'expected' => array(
                    'foo:bar' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name URL encoded should not conflict',
                'value' => 'foobar='.urlencode('&foo=bar'),
                'expected' => array(
                    'foobar' => '&foo=bar'
                )
            ),
            array(
                'label' => 'Direct from parse_url',
                'value' => parse_url('https://domain.com?foo=bar', PHP_URL_QUERY),
                'expected' => array(
                    'foo' => 'bar'
                )
            )
        );
        
        foreach($tests as $def)
        {
            $result = ConvertHelper::parseQueryString($def['value']);
            
            $this->assertEquals($def['expected'], $result, $def['label']);
        }
    }

    public function test_findString()
    {
        $tests = array(
            array(
                'label' => 'Empty needle',
                'haystack' => 'We were walking, and a foo appeared just like that.',
                'needle' => '',
                'expected' => array()
            ),
            array(
                'label' => 'No matches present',
                'haystack' => '',
                'needle' => 'foo',
                'expected' => array()
            ),
            array(
                'label' => 'One match present',
                'haystack' => 'We were walking, and a foo appeared just like that.',
                'needle' => 'foo',
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    )
                )
            ),
            array(
                'label' => 'One match present, different case',
                'haystack' => 'We were walking, and a Foo appeared just like that.',
                'needle' => 'foo',
                'expected' => array()
            ),
            array(
                'label' => 'One match present, different case, case insensitive',
                'haystack' => 'We were walking, and a Foo appeared just like that.',
                'needle' => 'foo',
                'caseInsensitive' => true,
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'Foo'
                    )
                )
            ),
            array(
                'label' => 'Several matches',
                'haystack' => 'We were walking, and a foo with another foo ran by, whith a foo trailing behind.',
                'needle' => 'foo',
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    ),
                    array(
                        'pos' => 40,
                        'match' => 'foo'
                    ),
                    array(
                        'pos' => 60,
                        'match' => 'foo'
                    )
                )
            ),
            array(
                'label' => 'Several matches, different cases',
                'haystack' => 'We were walking, and a foo with another Foo ran by, whith a fOo trailing behind.',
                'needle' => 'foo',
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    )
                )
            ),
            array(
                'label' => 'Several matches, different cases, case insensitive',
                'haystack' => 'We were walking, and a foo with another Foo ran by, whith a fOo trailing behind.',
                'needle' => 'foo',
                'caseInsensitive' => true,
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    ),
                    array(
                        'pos' => 40,
                        'match' => 'Foo'
                    ),
                    array(
                        'pos' => 60,
                        'match' => 'fOo'
                    )
                )
            ),
            array(
                'label' => 'One match using unicode characters',
                'haystack' => 'And a föö.',
                'needle' => 'föö',
                'expected' => array(
                    array(
                        'pos' => 6,
                        'match' => 'föö'
                    )
                )
            ),
            array(
                'label' => 'One match with a newline',
                'haystack' => 'And a\n foo.',
                'needle' => 'foo',
                'expected' => array(
                    array(
                        'pos' => 8,
                        'match' => 'foo'
                    )
                )
            )
        );
        
        foreach($tests as $test)
        {
            $caseInsensitive = false;
            if(isset($test['caseInsensitive'])) {
                $caseInsensitive = $test['caseInsensitive'];
            }
            
            $matches = ConvertHelper::findString($test['needle'], $test['haystack'], $caseInsensitive);
            
            $this->assertEquals(count($test['expected']), count($matches), 'Amount of matches should match.');
            
            foreach($matches as $idx => $match)
            {
                $testMatch = $test['expected'][$idx];
                
                $this->assertEquals($testMatch['pos'], $match->getPosition(), 'The position of needle should match.');
                $this->assertEquals($testMatch['match'], $match->getMatchedString(), 'The matched string should match.');
            }
        }
    }
    
    public function test_explodeTrim()
    {
        $tests = array(
            array(
                'label' => 'Empty string value',
                'delimiter' => ',',
                'string' => '',
                'expected' => array()
            ),
            array(
                'label' => 'Empty delimiter',
                'delimiter' => '',
                'string' => 'Some text here',
                'expected' => array()
            ),
            array(
                'label' => 'Comma delimiter, no spaces',
                'delimiter' => ',',
                'string' => 'foo,bar',
                'expected' => array(
                    'foo',
                    'bar'
                )
            ),
            array(
                'label' => 'Comma delimiter, with spaces',
                'delimiter' => ',',
                'string' => '  foo  ,  bar   ',
                'expected' => array(
                    'foo',
                    'bar'
                )
            ),
            array(
                'label' => 'Comma delimiter, with newlines',
                'delimiter' => ',',
                'string' => "  foo  \n,\n  bar\n   ",
                'expected' => array(
                    'foo',
                    'bar'
                )
            ),
            array(
                'label' => 'Comma delimiter, empty entries',
                'delimiter' => ',',
                'string' => ',foo,,bar,',
                'expected' => array(
                    'foo',
                    'bar'
                )
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::explodeTrim($test['delimiter'], $test['string']);
            
             $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_arrayRemoveKeys()
    {
        $tests = array(
            array(
                'label' => 'Keys not present in target array',
                'value' => array('bar' => 'foo'),
                'remove' => array('foo'),
                'expected' => array('bar' => 'foo')
            ),
            array(
                'label' => 'Remove assoc keys',
                'value' => array('foo' => 'bar', 'bar' => 'foo'),
                'remove' => array('foo', 'bar'),
                'expected' => array()
            ),
            array(
                'label' => 'Remove numeric keys',
                'value' => array('foo' => 'bar', 20 => 'foo'),
                'remove' => array(20),
                'expected' => array('foo' => 'bar')
            ),
            array(
                'label' => 'Loose key typing',
                'value' => array('foo' => 'bar', '20' => 'foo'),
                'remove' => array(20),
                'expected' => array('foo' => 'bar')
            ),
            array(
                'label' => 'Empty remove array',
                'value' => array('foo' => 'bar'),
                'remove' => array(),
                'expected' => array('foo' => 'bar')
            )
        );
        
        foreach($tests as $test)
        {
            $array = $test['value'];
            
            ConvertHelper::arrayRemoveKeys($array, $test['remove']);
            
            $this->assertEquals($test['expected'], $array, $test['label']);
        }
    }
}
