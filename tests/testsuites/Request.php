<?php

use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected function setUp() : void
    {
        $_REQUEST = array();
    }

    protected function tearDown() : void
    {
        $_REQUEST = array();
    }
    
    public function test_urlsMatch()
    {
        $tests = array(
            array(
                'label' => 'Same domains',
                'sourceUrl' => 'http://domain.com',
                'targetUrl' => 'http://domain.com',
                'match' => true
            ),
            array(
                'label' => 'Same domains, different scheme',
                'sourceUrl' => 'http://domain.com',
                'targetUrl' => 'https://domain.com',
                'match' => false
            ),
            array(
                'label' => 'Same domains, one with subdomain',
                'sourceUrl' => 'http://domain.com',
                'targetUrl' => 'http://www.domain.com',
                'match' => false
            ),
            array(
                'label' => 'Same domains, one with trailing slash',
                'sourceUrl' => 'http://domain.com/',
                'targetUrl' => 'http://domain.com',
                'match' => true
            ),
            array(
                'label' => 'Same domains, same paths',
                'sourceUrl' => 'http://domain.com/path',
                'targetUrl' => 'http://domain.com/path',
                'match' => true
            ),
            array(
                'label' => 'Same domains, different paths',
                'sourceUrl' => 'http://domain.com/path/to/page',
                'targetUrl' => 'http://domain.com/path/to/other',
                'match' => false
            ),
            array(
                'label' => 'Same domains, one with fragment',
                'sourceUrl' => 'http://domain.com#fragment',
                'targetUrl' => 'http://domain.com',
                'match' => true
            ),
            array(
                'label' => 'Same parameters',
                'sourceUrl' => 'http://domain.com?param1=yes',
                'targetUrl' => 'http://domain.com?param1=yes',
                'match' => true
            ),
            array(
                'label' => 'Same param names, different values',
                'sourceUrl' => 'http://domain.com?param1=yes',
                'targetUrl' => 'http://domain.com?param1=no',
                'match' => false
            ),
            array(
                'label' => 'Same params, different order',
                'sourceUrl' => 'http://domain.com?param1=yes&param2=yes',
                'targetUrl' => 'http://domain.com?param2=yes&param1=yes',
                'match' => true
            ),
            array(
                'label' => 'Same params, spaces and dots in param names',
                'sourceUrl' => 'http://domain.com?param.1=yes&param 2=yes&param_1=yes&param_2=yes',
                'targetUrl' => 'http://domain.com?param 2=yes&param.1=yes&param_2=yes&param_1=yes',
                'match' => true
            ),
            array(
                'label' => 'Same params, one with fragment',
                'sourceUrl' => 'http://domain.com?param1=yes&param2=yes#fragment',
                'targetUrl' => 'http://domain.com?param1=yes&param2=yes',
                'match' => true
            ),
            array(
                'label' => 'Same params with limited params',
                'sourceUrl' => 'http://domain.com?param1=yes&param2=yes&param3=bla',
                'targetUrl' => 'http://domain.com?param1=yes&param2=yes&param4=bla',
                'match' => true,
                'limitParams' => array('param1', 'param2')
            ),
            array(
                'label' => 'Different params with limited params',
                'sourceUrl' => 'http://domain.com?param1=yes&param2=yes&param3=bla',
                'targetUrl' => 'http://domain.com?param1=yes&param2=yes&param4=bla',
                'match' => false,
                'limitParams' => array('param1', 'param2', 'param3')
            ),
            array(
                'label' => 'No URLs',
                'sourceUrl' => 'domain.com',
                'targetUrl' => 'not an url at all',
                'match' => false
            ),
            array(
                'label' => 'Hosts only, without scheme',
                'sourceUrl' => 'domain.com',
                'targetUrl' => 'domain.com',
                'match' => true
            ),
            array(
                'label' => 'Hosts with paths, without scheme',
                'sourceUrl' => 'domain.com/path/to/page',
                'targetUrl' => 'domain.com/path/to/page',
                'match' => true
            ),
            array(
                'label' => 'One with fragment, fragments not ignored',
                'sourceUrl' => 'domain.com/path/to/page#fragment',
                'targetUrl' => 'domain.com/path/to/page',
                'match' => false,
                'ignoreFragments' => false
            ),
            array(
                'label' => 'Same URLs, but one with double slashes in path',
                'sourceUrl' => 'https://domain.com//path/to//page',
                'targetUrl' => 'https://domain.com/path/to/page',
                'match' => true,
            ),
        );
        
        $request = new AppUtils\Request();
        
        foreach($tests as $def)
        {
            $limitParams = array();
            if(isset($def['limitParams'])) {
                $limitParams = $def['limitParams'];
            }
            
            $comparer = $request->createURLComparer(
                $def['sourceUrl'], 
                $def['targetUrl'],
                $limitParams
            );
            
            if(isset($def['ignoreFragments']) && !$def['ignoreFragments']) {
                $comparer->setIgnoreFragment(false);
            }
            
            $result = $comparer->isMatch();
            
            $this->assertEquals($def['match'], $result, $def['label']);
        }
    }
    
   /**
    * Getting a parameter should return the expected value.
    * 
    * @see \AppUtils\Request::getParam()
    */
    public function test_getParam()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = 'bar';
        
        $this->assertEquals('bar', $request->getParam('foo'));
    }
    
   /**
    * Checking if a parameter exists depending on the
    * kind of value it is set to.
    * 
    * @see \AppUtils\Request::getParam()
    */
    public function test_paramExists()
    {
        $request = new \AppUtils\Request();
        
        $tests = array(
            array(
                'label' => 'Regular string',
                'value' => 'string',
                'expected' => true 
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => true
            ),
            array(
                'label' => 'NULL',
                'value' => null,
                'expected' => false
            ),
            array(
                'label' => 'Zero',
                'value' => 0,
                'expected' => true
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => true
            )
        );
        
        foreach($tests as $def) 
        {
            $_REQUEST['foo'] = $def['value'];
            
            $this->assertEquals($def['expected'], $request->hasParam('foo'), $def['label']);
        }
    }
    
   /**
    * Setting a parameter should have the expected value.
    * 
    * @see \AppUtils\Request::getParam()
    */
    public function test_setParam()
    {
        $request = new \AppUtils\Request();
        
        $request->setParam('foo', 'new');
        
        $this->assertEquals('new', $request->getParam('foo'));
        $this->assertEquals('new', $_REQUEST['foo']);
    }
    
   /**
    * Setting a parameter should overwrite any existing value.
    * 
    * @see \AppUtils\Request::getParam()
    */
    public function test_setParam_overwrite()
    {
        $request = new \AppUtils\Request();
        
        // set a value before we try to set it
        $_REQUEST['foo'] = 'bar';
        
        $request->setParam('foo', 'new');
        
        $this->assertEquals('new', $request->getParam('foo'));
        $this->assertEquals('new', $_REQUEST['foo']);
    }
    
   /**
    * Removing a parameter should remove it also from the request array.
    * 
    * @see \AppUtils\Request::removeParam()
    */
    public function test_removeParam()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = 'bar';
        
        $request->removeParam('foo');
        
        $this->assertFalse($request->hasParam('foo'), 'Parameter should not exist after removing it.');
        $this->assertFalse(isset($_REQUEST['foo']), 'Parameter should not exist in request array after removing it.');
    }
    
   /**
    * Removing a parameter should also remove its registration
    * if it had been previously registered.
    * 
    * @see \AppUtils\Request::removeParam()
    */
    public function test_removeParam_registered()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = 'bar';
        
        $request->registerParam('foo')->setAlpha();
        
        $request->removeParam('foo');
        
        $this->assertFalse($request->hasRegisteredParam('foo'), 'Removing a parameter should remove its registration as well.');
    }
    
   /**
    * Fetching a JSON parameter as a decoded array.
    *
    * @see \AppUtils\Request::getJSON()
    */
    public function test_getJSON()
    {
        $request = new \AppUtils\Request();
        
        $data = array(
            'foo' => 'bar'
        );
        
        $_REQUEST['foo'] = json_encode($data);
        
        $testData = $request->getJSON('foo');
        
        $this->assertEquals($data, $testData, 'Get parameter as decoded JSON.');
    }
    
   /**
    * Trying to fetch a parameter as JSON when it 
    * is empty or does not exist.
    * 
    * @see \AppUtils\Request::getJSON()
    */
    public function test_getJSON_empty()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = '';
        
        $this->assertEquals(null, $request->getJSON('bar'), 'Empty if parameter does not exist.');
        $this->assertEquals(null, $request->getJSON('foo'), 'Empty if parameter is empty.');
    }

   /**
    * Trying to fetch a request parameter as JSON 
    * when it is not a valid JSON string.
    * 
    * @see \AppUtils\Request::getJSON()
    */
    public function test_getJSON_broken()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = 'bar'; 
        
        $result = $request->getJSON('foo');
        
        $this->assertEquals(null, $result, 'Empty if parameter is not a JSON string.');
    }
    
   /**
    * Registrering a parameter must return a parameter instance.
    * 
    * @see \AppUtils\Request::registerParam()
    */
    public function test_registerParam()
    {
        $request = new \AppUtils\Request();
        
        $param = $request->registerParam('foo');
        
        $this->assertInstanceOf(\AppUtils\Request_Param::class, $param, 'Register must return param instance.');
    }
    
   /**
    * Registering a parameter without specifying a format
    * should act like getting it without registering it.
    * 
    * @see \AppUtils\Request::registerParam()
    */
    public function test_getRegisteredParam()
    {
        $request = new \AppUtils\Request();
        
        $tests = array(
            array(
                'label' => 'Regular string',
                'value' => 'string',
                'expected' => 'string'
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'NULL',
                'value' => null,
                'expected' => null
            ),
            array(
                'label' => 'Zero',
                'value' => 0,
                'expected' => 0
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => '0'
            )
        );
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $this->assertEquals($def['expected'], $request->registerParam($name)->get(), $def['label']);
        }
    }
    
   /**
    * Fetching a valid integer value should return the
    * expected integer string.
    */
    public function test_getRegisteredParam_integer()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = '100';
        
        $value = $request->registerParam('foo')->setInteger()->get();
        
        $this->assertEquals('100', $value);
    }
    
   /**
    * An invalid integer value should not return any value at all.
    */
    public function test_getRegisteredParam_integer_invalid()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = 'not-an-integer';
        
        $value = $request->registerParam('foo')->setInteger()->get();
        
        $this->assertEquals(null, $value, 'A non-integer value should return null.');
    }

   /**
    * Specifying a default value should return that value if
    * the value in the request is invalid.
    */
    public function test_getRegisteredParam_integer_invalid_default()
    {
        $request = new \AppUtils\Request();
        
        $_REQUEST['foo'] = 'not-an-integer';
        
        $value = $request->registerParam('foo')->setInteger()->get(0);
        
        $this->assertEquals(0, $value, 'A non-integer value should return the specified default value.');
    }
    
   /**
    * When getting a default value, it has to be validated as well. 
    */
    public function test_getRegisteredParam_integer_default_invalid()
    {
        $request = new \AppUtils\Request();
        
        $value = $request->registerParam('foo')->setInteger()->get('not-an-integer');
        
        $this->assertEquals(null, $value, 'An invalid default value should return null.');
    }
    
   /**
    * Specifying possible values should return only those values.
    */
    public function test_getRegisteredParam_enum()
    {
        $tests = array(
            array(
                'label' => 'Value exists, and is in accepted values list.',
                'value' => 'bar',
                'accepted' => array('bar', 'foo', 'gnu'),
                'expected' => 'bar',
            ),
            array(
                'label' => 'Value exists, but is not in accepted values list.',
                'value' => 'bar',
                'accepted' => array('foo', 'gnu'),
                'expected' => null
            ),
            array(
                'label' => 'No value specified in request.',
                'value' => '',
                'accepted' => array('foo', 'bar'),
                'expected' => null
            ),
            array(
                'label' => 'The default value is used when an invalid value is specified.',
                'value' => 'invalid',
                'accepted' => array('foo', 'bar'),
                'expected' => 'foo',
                'default' => 'foo'
            ),
            array(
                'label' => 'The default value must also be in the accepted values.',
                'value' => 'invalid',
                'accepted' => array('foo', 'bar'),
                'expected' => null,
                'default' => 'invalid'
            )
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $default = null;
            if(isset($def['default'])) {
                $default = $def['default'];
            }
            
            $value = $request->registerParam($name)
            ->setEnum($def['accepted'])
            ->get($default);
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filter_stripWhitespace()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => ''
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo'),
                'expected' => ''
            ),
            array(
                'label' => 'Object value',
                'value' => new stdClass(),
                'expected' => ''
            ),
            array(
                'label' => 'Single space',
                'value' => ' ',
                'expected' => ''
            ),
            array(
                'label' => 'Texts with several spaces between',
                'value' => 'foo       bar',
                'expected' => 'foobar'
            ),
            array(
                'label' => 'Text with spaces around it',
                'value' => '   foo   ',
                'expected' => 'foo'
            ),
            array(
                'label' => 'Text with tabs and newlines',
                'value' => "\t foo \r \n", 
                'expected' => 'foo'
            )
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->addStripWhitespaceFilter()
            ->get('');
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_getRegisteredParam_idsList()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => array()
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => array()
            ),
            array(
                'label' => 'Invalid string value',
                'value' => 'invalid',
                'expected' => array()
            ),
            array(
                'label' => 'Single ID value',
                'value' => '5',
                'expected' => array(5)
            ),
            array(
                'label' => 'Single ID value with spaces around it',
                'value' => '   5   ',
                'expected' => array(5)
            ),
            array(
                'label' => 'Multiple ID values',
                'value' => '5,14,20,79',
                'expected' => array(5, 14, 20, 79)
            ),
            array(
                'label' => 'Stripping whitespace',
                'value' => '5,    89    , 21',
                'expected' => array(5, 89, 21)
            ),
            array(
                'label' => 'Mixing valid and invalid values',
                'value' => '5, invalid, something, 50',
                'expected' => array(5, 50)
            ),
            array(
                'label' => 'List with newlines and tabs',
                'value' => "\t5,\n\t50\n",
                'expected' => array(5, 50)
            ),
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->setIDList()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filterTrim()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => ''
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'String with spaces',
                'value' => '   bar   ',
                'expected' => 'bar'
            ),
            array(
                'label' => 'String with newlines',
                'value' => "\rbar\n",
                'expected' => 'bar'
            ),
            array(
                'label' => 'String with tabs and spaces',
                'value' => "\tbar   ",
                'expected' => 'bar'
            ),
            array(
                'label' => 'String without spaces',
                'value' => "foo bar",
                'expected' => 'foo bar'
            ),
            array(
                'label' => 'Array value',
                'value' => array(),
                'expected' => ''
            ),
            array(
                'label' => 'Object value',
                'value' => new \AppUtils\Request(),
                'expected' => ''
            )
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->addFilterTrim()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filterString()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => ''
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'String with spaces',
                'value' => 'bar',
                'expected' => 'bar'
            ),
            array(
                'label' => 'String with newlines',
                'value' => "\rbar\n",
                'expected' => "\rbar\n"
            ),
            array(
                'label' => 'String with tabs and spaces',
                'value' => "\t  bar   ",
                'expected' => "\t  bar   "
            ),
            array(
                'label' => 'Integer value',
                'value' => 10,
                'expected' => '10'
            ),
            array(
                'label' => 'Float value',
                'value' => 10.85,
                'expected' => '10.85'
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo'),
                'expected' => ''
            ),
            array(
                'label' => 'Object value',
                'value' => new \AppUtils\Request(),
                'expected' => ''
            )
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->addStringFilter()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filterStripTags()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => NULL,
                'expected' => ''
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo'),
                'expected' => ''
            ),
            array(
                'label' => 'Object value',
                'value' => new stdClass(),
                'expected' => ''
            ),
            array(
                'label' => 'Simple tag',
                'value' => '<b>Text',
                'expected' => 'Text'
            ),
            array(
                'label' => 'Self-closed tag',
                'value' => '<br/>Text',
                'expected' => 'Text'
            ),
            array(
                'label' => 'Non-HTML brackets',
                'value' => 'Click here >',
                'expected' => 'Click here >'
            ),
            array(
                'label' => 'Several tags',
                'value' => '<b>Text</b> And a link <a href="http://github.com">hoho</a>',
                'expected' => 'Text And a link hoho'
            )
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->addStripTagsFilter()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_getRegisteredParam_boolean()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => false
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => false
            ),
            array(
                'label' => 'Invalid string value',
                'value' => 'invalid',
                'expected' => false
            ),
            array(
                'label' => 'Valid string false value',
                'value' => 'false',
                'expected' => false
            ),
            array(
                'label' => 'Valid string true value',
                'value' => 'true',
                'expected' => true
            ),
            array(
                'label' => 'Valid string true value, alternate yes/no',
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'label' => 'Array value',
                'value' => array(),
                'expected' => false
            ),
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->setBoolean()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_getBool()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => false
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => false
            ),
            array(
                'label' => 'Invalid string value',
                'value' => 'invalid',
                'expected' => false
            ),
            array(
                'label' => 'Valid string false value',
                'value' => 'false',
                'expected' => false
            ),
            array(
                'label' => 'Valid string true value',
                'value' => 'true',
                'expected' => true
            ),
            array(
                'label' => 'Valid string true value, alternate yes/no',
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'label' => 'Array value',
                'value' => array(),
                'expected' => false
            ),
            array(
                'label' => 'Valid string zero (0)',
                'value' => '0',
                'expected' => false
            ),
            array(
                'label' => 'Valid numeric zero (0)',
                'value' => 0,
                'expected' => false
            ),
            array(
                'label' => 'Valid string one (1)',
                'value' => '1',
                'expected' => true
            ),
            array(
                'label' => 'Valid numeric one (1)',
                'value' => 1,
                'expected' => true
            ),
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->getBool($name);
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filter_commaSeparated()
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => array()
            ),
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => array()
            ),
            array(
                'label' => 'Boolean value',
                'value' => true,
                'expected' => array()
            ),
            array(
                'label' => 'Array values get passed through unchanged',
                'value' => array('something', 'schmomthing', 50),
                'expected' => array('something', 'schmomthing', 50)
            ),
            array(
                'label' => 'Single space value',
                'value' => ' ',
                'expected' => array()
            ),
            array(
                'label' => 'Single space value with trim OFF',
                'value' => ' ',
                'expected' => array(' '),
                'trim' => false
            ),
            array(
                'label' => 'Comma separated values, no spaces',
                'value' => 'foo,bar',
                'expected' => array('foo', 'bar')
            ),
            array(
                'label' => 'Comma separated values, with spaces',
                'value' => '  foo  ,  bar   ',
                'expected' => array('foo', 'bar')
            ),
            array(
                'label' => 'Comma separated values, with spaces, trim OFF',
                'value' => ' foo , bar ',
                'expected' => array(' foo ', ' bar '),
                'trim' => false
            ),
            array(
                'label' => 'Comma separated values, empty entries',
                'value' => 'foo,,,bar',
                'expected' => array('foo', 'bar')
            ),
            array(
                'label' => 'Comma separated values, empty entries, strip OFF',
                'value' => 'foo,,,bar',
                'expected' => array('foo', '', '', 'bar'),
                'strip' => false
            ),
            array(
                'label' => 'Comma separated values, empty entries, strip and trim OFF',
                'value' => ' foo , bar ,,',
                'expected' => array(' foo ', ' bar ', '', ''),
                'trim' => false,
                'strip' => false
            )
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $trim = true; if(isset($def['trim'])) { $trim = $def['trim']; }
            $strip = true; if(isset($def['strip'])) { $strip = $def['strip']; }
            
            $value = $request->registerParam($name)
            ->addCommaSeparatedFilter($trim, $strip)
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_getRegisteredParam_commaSeparated()
    {
        $tests = array(
            array(
                'label' => 'Comma separated values',
                'value' => 'foo,bar,lopos',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo', 'bar'),
            ),
            array(
                'label' => 'Comma separated values, with empty entries',
                'value' => 'foo,bar,lopos,,,',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo', 'bar'),
            ),
            array(
                'label' => 'Comma separated values, with trim OFF',
                'value' => 'foo,  bar,  lopos',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo'),
                'trim' => false
            ),
            array(
                'label' => 'Comma separated values, with strip OFF',
                'value' => 'foo,bar, ,lopos',
                'allowed' => array('foo', 'bar'),
                'expected' => array('foo', 'bar'),
                'strip' => false
            )
        );
        
        $request = new \AppUtils\Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $trim = true; if(isset($def['trim'])) { $trim = $def['trim']; }
            $strip = true; if(isset($def['strip'])) { $strip = $def['strip']; }
            
            $value = $request->registerParam($name)
            ->addCommaSeparatedFilter($trim, $strip)
            ->setValuesList($def['allowed'])
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_getAcceptHeaders()
    {
        // simulate the accept header string
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3';
        
        $headers = \AppUtils\Request::getAcceptHeaders();
        
        $this->assertEquals(
            array(
                'application/xml',
                '*/*',
                'text/html',
                'application/xhtml+xml',
                'image/webp',
                'image/apng',
                'application/signed-exchange'
            ), 
            $headers
        );
    }
    
    protected function setUniqueParam($value) : string
    {
        $name = $this->generateUniqueParamName();
        $_REQUEST[$name] = $value;
        
        return $name;
    }
    
    protected $paramCounter = 0;
    
    protected function generateUniqueParamName() : string
    {
        $this->paramCounter++;
        
        return 'foo'.$this->paramCounter;
    }
}
