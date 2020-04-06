<?php

use PHPUnit\Framework\TestCase;

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\ConvertHelper_SizeNotation;
use AppUtils\ConvertHelper_StorageSizeEnum;

final class ConvertHelperTest extends TestCase
{
    protected $assetsFolder;
    
    protected function setUp() : void
    {
        if(isset($this->assetsFolder))
        {
            return;
        }
        
        $this->assetsFolder = realpath(TESTS_ROOT.'/assets/ConvertHelper');
        
        if($this->assetsFolder === false) 
        {
            throw new Exception(
                'The convert helper assets folder could not be found.'
            );
        }
    }
    
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
    
    public function test_string2bool()
    {
        $tests = array(
            array(
                'value' => 0,
                'expected' => false
            ),
            array(
                'value' => 1,
                'expected' => true
            ),
            array(
                'value' => '0',
                'expected' => false
            ),
            array(
                'value' => '1',
                'expected' => true
            ),
            array(
                'value' => false,
                'expected' => false
            ),
            array(
                'value' => true,
                'expected' => true
            ),
            array(
                'value' => 'false',
                'expected' => false
            ),
            array(
                'value' => 'true',
                'expected' => true
            ),
            array(
                'value' => 'no',
                'expected' => false
            ),
            array(
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'value' => null,
                'expected' => false
            ),
            array(
                'value' => array(),
                'expected' => false
            ),
            array(
                'value' => new stdClass(),
                'expected' => false
            )
        );
        
        foreach($tests as $test)
        {
            $actual = ConvertHelper::string2bool($test['value']);
            
            $this->assertSame($test['expected'], $actual);
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
                'string' => '',
                'result' => array()
            ),
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
                'label' => 'Parameter name surrounded by spaces',
                'value' => '  foo  =result',
                'expected' => array(
                    '  foo  ' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name surrounded by pre-encoded spaces',
                'value' => '%20%20foo%20%20=result',
                'expected' => array(
                    '  foo  ' => 'result'
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
    
    public function test_date2timestamp()
    {
        $timestamp = mktime(10, 15, 0, 2, 2, 2006);
        $date = ConvertHelper::timestamp2date($timestamp);
        
        $back = ConvertHelper::date2timestamp($date);
        
        $this->assertEquals($timestamp, $back);
    }
    
    public function test_isInteger()
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => false
            ),
            array(
                'label' => 'Numeric Zero',
                'value' => 0,
                'expected' => true
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => true
            ),
            array(
                'label' => 'Numeric 1',
                'value' => 1,
                'expected' => true
            ),
            array(
                'label' => 'Numeric -50',
                'value' => -50,
                'expected' => true
            ),
            array(
                'label' => 'String -50',
                'value' => '-50',
                'expected' => true
            ),
            array(
                'label' => 'Boolean true',
                'value' => true,
                'expected' => false
            ),
            array(
                'label' => 'Array',
                'value' => array('foo' => 'bar'),
                'expected' => false
            ),
            array(
                'label' => 'Object',
                'value' => new stdClass(),
                'expected' => false
            ),
            array(
                'label' => 'Integer value 145',
                'value' => 145,
                'expected' => true
            ),
            array(
                'label' => 'Integer value 1000',
                'value' => 1000,
                'expected' => true
            ),
            array(
                'label' => 'String integer',
                'value' => '1458',
                'expected' => true
            ),
            array(
                'label' => 'Decimal value',
                'value' => 10.45,
                'expected' => false
            ),
            array(
                'label' => 'String decimal',
                'value' => '10.89',
                'expected' => false
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::isInteger($test['value']);
            
            $this->assertSame($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_seconds2interval()
    {
        $tests = array(
            array(
                'label' => '60 seconds = 1 minute',
                'seconds' => 60,
                'expected' => array(
                    'seconds' => 0,
                    'minutes' => 1,
                    'hours' => 0,
                    'days' => 0
                )
            ),
            array(
                'label' => '59 seconds = 59 seconds',
                'seconds' => 59,
                'expected' => array(
                    'seconds' => 59,
                    'minutes' => 0,
                    'hours' => 0,
                    'days' => 0
                )
            ),
            array(
                'label' => '3601 seconds = 1 hour, 1 second',
                'seconds' => 3601,
                'expected' => array(
                    'seconds' => 1,
                    'minutes' => 0,
                    'hours' => 1,
                    'days' => 0
                )
            )
        );
        
        foreach($tests as $test)
        {
            $interval = ConvertHelper::seconds2interval($test['seconds']);
            
            $this->assertEquals($test['expected']['seconds'], $interval->s, $test['label']);
            $this->assertEquals($test['expected']['minutes'], $interval->i, $test['label']);
            $this->assertEquals($test['expected']['hours'], $interval->h, $test['label']);
            $this->assertEquals($test['expected']['days'], $interval->d, $test['label']);
        }
    }
    
    public function test_interval2total()
    {
        $tests = array(
            array(
                'label' => '100 seconds',
                'value' => ConvertHelper::seconds2interval(100),
                'expected' => 100,
                'units' => ConvertHelper::INTERVAL_SECONDS
            ),
            array(
                'label' => '3600 seconds',
                'value' => ConvertHelper::seconds2interval(3600),
                'expected' => 1,
                'units' => ConvertHelper::INTERVAL_HOURS
            ),
            array(
                'label' => '3 minutes and some seconds',
                'value' => ConvertHelper::seconds2interval(60*3+15),
                'expected' => 3,
                'units' => ConvertHelper::INTERVAL_MINUTES
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::interval2total($test['value'], $test['units']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_var2json()
    {
        $tests = array(
            array(
                'label' => 'Regular array',
                'value' => array('foo'),
                'expected' => '["foo"]'
            ),
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::var2json($test['value']);
            
            $this->assertEquals($test['expected'], $result);
        }
    }
    
    public function test_var2json_error()
    {
        $this->expectException(ConvertHelper_Exception::class);
        
        // the paragraph sign cannot be converted to JSON.
        $result = ConvertHelper::var2json(array(utf8_decode('öäöü§')));
        
        
    }
    
    public function test_duration2string()
    {
        $time = time();
        
        $tests = array(
            array(
                'label' => '60 seconds ago',
                'from' => $time - 60,
                'to' => $time,
                'expected' => 'One minute ago'
            ),
            array(
                'label' => 'No to time set',
                'from' => $time - 60,
                'to' => -1,
                'expected' => 'One minute ago'
            ),
            array(
                'label' => 'Future time',
                'from' => $time + 60,
                'to' => $time,
                'expected' => 'In one minute'
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::duration2string($test['from'], $test['to']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_intervalstring()
    {
        $tests = array(
            array(
                'label' => '60 seconds',
                'interval' => new DateInterval('PT60S'),
                'expected' => '1 minute'
            ),
            array(
                'label' => '1 hour 25 seconds',
                'interval' => new DateInterval('PT'.(60*60+25).'S'),
                'expected' => '1 hour and 25 seconds'
            ),
            array(
                'label' => '6 days',
                'interval' => new DateInterval('PT'.(60*60*24*6).'S'),
                'expected' => '6 days'
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::interval2string($test['interval']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_size2bytes()
    {
        $tests = array(
            array(
                'label' => 'Zero value',
                'value' => '0',
                'expected' => 0
            ),
            array(
                'label' => '1 value',
                'value' => '1',
                'expected' => 1
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => 0
            ),
            array(
                'label' => 'Negative value',
                'value' => '-100',
                'expected' => 0
            ),
            array(
                'label' => 'No units, integer',
                'value' => '500',
                'expected' => 500
            ),
            array(
                'label' => 'No units, float',
                'value' => '500.45',
                'expected' => 500
            ),
            array(
                'label' => 'No units, float, comma notation',
                'value' => '500,45',
                'expected' => 500
            ),
            array(
                'label' => 'Invalid string',
                'value' => 'Some text here',
                'expected' => 0
            ),
            array(
                'label' => 'Byte units, negative',
                'value' => '-500B',
                'expected' => 0
            ),
            array(
                'label' => 'Byte units',
                'value' => '500B',
                'expected' => 500
            ),
            array(
                'label' => 'Byte units, spaces',
                'value' => '   500     B     ',
                'expected' => 500
            ),
            array(
                'label' => 'Kilobytes',
                'value' => '1KB',
                'expected' => 1000
            ),
            array(
                'label' => 'Megabytes',
                'value' => '1MB',
                'expected' => 1000000
            ),
            array(
                'label' => 'Gigabytes',
                'value' => '1GB',
                'expected' => 1000000000
            ),
            array(
                'label' => 'iKilobytes',
                'value' => '1KiB',
                'expected' => 1024
            ),
            array(
                'label' => 'iMegabytes',
                'value' => '1MiB',
                'expected' => 1048576
            ),
            array(
                'label' => 'iGigabytes',
                'value' => '1GiB',
                'expected' => 1073741824
            ),
            array(
                'label' => 'iKilobytes, case insensitive',
                'value' => '1kib',
                'expected' => 1024
            ),
            array(
                'label' => 'Several units',
                'value' => '1 KB GiB',
                'expected' => 0
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::size2bytes($test['value']);
            
            $this->assertSame($test['expected'], $result, $test['label']);
        }
    }
    
     public function test_parseSize() 
    {
        $size = ConvertHelper::parseSize('50MB');
        
        $this->assertInstanceOf(ConvertHelper_SizeNotation::class, $size);
    }
    
    public function test_parseSize_errors()
    {
        $tests = array(
            array(
                'label' => 'Negative value',
                'value' => '-100',
                'error' => ConvertHelper_SizeNotation::VALIDATION_ERROR_NEGATIVE_VALUE
            ),
            array(
                'label' => 'Invalid string',
                'value' => 'Some text here',
                'error' => ConvertHelper_SizeNotation::VALIDATION_ERROR_UNRECOGNIZED_STRING
            ),
            array(
                'label' => 'Several units',
                'value' => '1 KB GiB',
                'error' => ConvertHelper_SizeNotation::VALIDATION_ERROR_MULTIPLE_UNITS
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::parseSize($test['value']);
            
            $this->assertFalse($result->isValid(), $test['label']);
            $this->assertSame($test['error'], $result->getErrorCode(), $test['label']);
        }
    }
    
    public function test_bytes2readable()
    {
        $tests = array(
            array(
                'label' => 'Negative value',
                'value' => -100,
                'result' => '0 B'
            ),
            array(
                'label' => 'Max byte value',
                'value' => 999,
                'result' => '999 B'
            ),
            array(
                'label' => 'KB value',
                'value' => 1000,
                'result' => '1 KB'
            ),
            array(
                'label' => 'KB value',
                'value' => 1500,
                'result' => '1.5 KB'
            ),
            array(
                'label' => 'MB value',
                'value' => 1400000,
                'result' => '1.4 MB'
            ),
            array(
                'label' => 'GB value',
                'value' => 1400000000,
                'result' => '1.4 GB'
            ),
            array(
                'label' => 'TB value',
                'value' => 1400000000000,
                'result' => '1.4 TB'
            ),
            array(
                'label' => 'PB value',
                'value' => 1400000000000000,
                'result' => '1.4 PB'
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::bytes2readable($test['value']);
            
            $this->assertSame($test['result'], $result, $test['label']);
        }
    }
    
    public function test_bytes2readable_precision()
    {
        $tests = array(
            array(
                'label' => 'Rounding up',
                'value' => 1800,
                'result' => '2 KB',
                'precision' => 0
            ),
            array(
                'label' => 'Rounding down',
                'value' => 1400,
                'result' => '1 KB',
                'precision' => 0
            ),
            array(
                'label' => 'Higher precision',
                'value' => 1480,
                'result' => '1.48 KB',
                'precision' => 2
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::bytes2readable($test['value'], $test['precision']);
            
            $this->assertSame($test['result'], $result, $test['label']);
        }
    }
    
    public function test_bytes2readable_base2()
    {
        $tests = array(
            array(
                'label' => 'Max byte value',
                'value' => 1023,
                'result' => '1023 B',
            ),
            array(
                'label' => '0 value',
                'value' => 0,
                'result' => '0 B',
            ),
            array(
                'label' => '1 value',
                'value' => 1,
                'result' => '1 B',
            ),
            array(
                'label' => 'KiB value',
                'value' => 1024,
                'result' => '1 KiB',
            ),
            array(
                'label' => 'MiB value',
                'value' => 1024 ** 2,
                'result' => '1 MiB',
            ),
            array(
                'label' => 'GiB value',
                'value' => 1024 ** 3,
                'result' => '1 GiB',
            ),
            array(
                'label' => 'TiB value',
                'value' => 1024 ** 4,
                'result' => '1 TiB',
            ),
            array(
                'label' => 'PiB value',
                'value' => 1024 ** 5,
                'result' => '1 PiB',
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::bytes2readable($test['value'], 0, ConvertHelper_StorageSizeEnum::BASE_2);
            
            $this->assertSame($test['result'], $result, $test['label']);
        }
    }
    
    public function test_storageSizeEnum_localeSwitching()
    {
        if(!class_exists('\AppLocalize\Localization')) 
        {
            $this->markTestSkipped('The localization package is not installed.');
        }
        
        $size = ConvertHelper_StorageSizeEnum::getSizeByName('mb');
        
        $this->assertEquals('Megabyte', $size->getLabelSingular());
        
        \AppLocalize\Localization::addAppLocale('fr_FR');
        \AppLocalize\Localization::selectAppLocale('fr_FR');
        
        $size = ConvertHelper_StorageSizeEnum::getSizeByName('mb');
        
        $this->assertEquals('mégaoctet', $size->getLabelSingular());
        
        \AppLocalize\Localization::reset();
    }
    
    public function test_spaces2tabs()
    {
        $tests = array(
            array(
                'label' => 'No spaces',
                'value' => "Foo",
                'expected' => "Foo"
            ),
            array(
                'label' => 'Three spaces indentation',
                'value' => "   Foo",
                'expected' => "   Foo"
            ),
            array(
                'label' => 'Four spaces indentation',
                'value' => "    Foo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Seven spaces indentation',
                'value' => "       Foo",
                'expected' => "\t   Foo"
            ),
            array(
                'label' => 'Tabbed string',
                'value' => "\tFoo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Different spaces mix',
                'value' =>
                "    Foo".PHP_EOL.
                "Foo    ",
                'expected' =>
                "\tFoo".PHP_EOL.
                "Foo\t"
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::spaces2tabs($test['value']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_hidden2visible()
    {
        $tests = array(
            array(
                'label' => 'Spaces and newlines',
                'value' => " \n\r\t",
                'expected' => "[SPACE][LF][CR][TAB]"
            ),
            array(
                'label' => 'Control characters',
                'value' => "\x00\x0D\x15",
                'expected' => "[NUL][CR][NAK]"
            ),
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::hidden2visible($test['value']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_normalizeTabs()
    {
        $tests = array(
            array(
                'label' => 'Two spaces indentation',
                'value' => "  Foo",
                'expected' => "  Foo"
            ),
            array(
                'label' => 'Four spaces indentation',
                'value' => "    Foo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Seven spaces indentation',
                'value' => "       Foo",
                'expected' => "\t   Foo"
            ),
            array(
                'label' => 'One-tabbed string',
                'value' => "\tFoo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Different tabs mix',
                'value' => 
                    "\t\t\tFoo".PHP_EOL.
                    "\tFoo",
                'expected' => 
                    "\t\tFoo".PHP_EOL.
                    "Foo"
            )
        );
        
        foreach($tests as $test)
        {
            $result = ConvertHelper::normalizeTabs($test['value']);
            
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
    
    public function test_stripControlChars()
    {
        $string = file_get_contents($this->assetsFolder.'/ControlCharacters.txt');
        
        $result = ConvertHelper::stripControlCharacters($string);
            
        $this->assertEquals('SOHACKBELL', $result);
    }
    
   /**
    * Ensure that the automatic fixing of UTF8 characters works as intended.
    */
    public function test_stripControlChars_brokenUTF8()
    {
        $string = file_get_contents($this->assetsFolder.'/ControlCharactersBrokenUTF8.txt');
        
        $result = ConvertHelper::stripControlCharacters($string);
        
        $this->assertEquals('SOHACKBELLöäüYes', $result);
    }
}
