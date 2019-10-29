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
            )
        );
        
        foreach($tests as $def)
        {
            $result = ConvertHelper::parseQueryString($def['value']);
            
            $this->assertEquals($def['expected'], $result, $def['label']);
        }
    }
}
