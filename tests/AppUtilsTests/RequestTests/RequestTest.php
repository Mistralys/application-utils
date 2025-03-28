<?php

declare(strict_types=1);

namespace AppUtilsTests\RequestTests;

use AppUtils\Request;
use AppUtils\Request_AcceptHeaders;
use AppUtilsTests\TestClasses\RequestTestCase;
use stdClass;

final class RequestTest extends RequestTestCase
{
    // region: _Tests

    public function test_urlsMatch() : void
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
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $limitParams = $def['limitParams'] ?? array();

            $comparer = $request->createURLComparer(
                $def['sourceUrl'], 
                $def['targetUrl'],
                $limitParams
            );
            
            if(isset($def['ignoreFragments']))
            {
                $comparer->setIgnoreFragment($def['ignoreFragments']);
            }
            
            $result = $comparer->isMatch();
            
            $this->assertEquals($def['match'], $result, $def['label']);
        }
    }
    
   /**
    * Getting a parameter should return the expected value.
    * 
    * @see Request::getParam()
    */
    public function test_getParam() : void
    {
        $request = new Request();
        
        $_REQUEST['foo'] = 'bar';
        
        $this->assertEquals('bar', $request->getParam('foo'));
    }
    
   /**
    * Checking if a parameter exists depending on the
    * kind of value.
    * 
    * @see Request::getParam()
    */
    public function test_paramExists() : void
    {
        $request = new Request();
        
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
    * @see Request::getParam()
    */
    public function test_setParam() : void
    {
        $request = new Request();
        
        $request->setParam('foo', 'new');
        
        $this->assertEquals('new', $request->getParam('foo'));
        $this->assertEquals('new', $_REQUEST['foo']);
    }
    
   /**
    * Setting a parameter should overwrite any existing value.
    * 
    * @see Request::getParam()
    */
    public function test_setParam_overwrite() : void
    {
        $request = new Request();
        
        // set a value before we try to set it
        $_REQUEST['foo'] = 'bar';
        
        $request->setParam('foo', 'new');
        
        $this->assertEquals('new', $request->getParam('foo'));
        $this->assertEquals('new', $_REQUEST['foo']);
    }
    
   /**
    * Removing a parameter should remove it also from the request array.
    * 
    * @see Request::removeParam()
    */
    public function test_removeParam() : void
    {
        $request = new Request();
        
        $_REQUEST['foo'] = 'bar';
        
        $request->removeParam('foo');
        
        $this->assertFalse($request->hasParam('foo'), 'Parameter should not exist after removing it.');
        $this->assertFalse(isset($_REQUEST['foo']), 'Parameter should not exist in request array after removing it.');
    }
    
   /**
    * Removing a parameter should also remove its registration
    * if it had been previously registered.
    * 
    * @see Request::removeParam()
    */
    public function test_removeParam_registered() : void
    {
        $request = new Request();
        
        $_REQUEST['foo'] = 'bar';
        
        $request->registerParam('foo')->setAlpha();
        
        $request->removeParam('foo');
        
        $this->assertFalse($request->hasRegisteredParam('foo'), 'Removing a parameter should remove its registration as well.');
    }

    public function test_removeParams() : void
    {
        $request = new Request();

        $_REQUEST['foo'] = 'bar';
        $_REQUEST['bar'] = 'foo';

        $request->removeParams(array('foo', 'bar'));

        $this->assertArrayNotHasKey('foo', $_REQUEST);
        $this->assertArrayNotHasKey('bar', $_REQUEST);
    }
    
   /**
    * Fetching a JSON parameter as a decoded array.
    *
    * @see Request::getJSON()
    */
    public function test_getJSON() : void
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
                'label' => 'Array value',
                'value' => array('foo' => 'bar'),
                'expected' => array()
            ),
            array(
                'label' => 'Array JSON',
                'value' => '["foo", "bar"]',
                'expected' => array('foo', 'bar')
            ),
            array(
                'label' => 'Object JSON',
                'value' => '{"foo":"bar"}',
                'expected' => array('foo' => 'bar')
            ),
            array(
                'label' => 'Invalid JSON',
                'value' => '{foo:"bar"}',
                'expected' => array()
            ),
            array(
                'label' => 'Return value as object',
                'value' => '{"foo":"bar"}',
                'assoc' => false,
                'expected' => (object) array('foo' => 'bar')
            ),
            array(
                'label' => 'Empty value as object',
                'value' => '',
                'assoc' => false,
                'expected' => new stdClass()
            ),
            array(
                'label' => 'Numeric value',
                'value' => '500',
                'expected' => array()
            ),
            array(
                'label' => 'Quoted string',
                'value' => '"foo"',
                'expected' => array()
            ),
            array(
                'label' => 'Numeric value',
                'value' => '500',
                'assoc' => false,
                'expected' => new stdClass()
            ),
            array(
                'label' => 'Quoted string',
                'value' => '"foo"',
                'assoc' => false,
                'expected' => new stdClass()
            )
        );
        
        $request = new Request();
       
        foreach($tests as $test) 
        {
            $name = $this->setUniqueParam($test['value']);

            $assoc = $test['assoc'] ?? true;

            $value = $request->getJSON($name, $assoc);
            
            $this->assertEquals($test['expected'], $value, $test['label']);
        }
    }
    

    
    public function test_filterTrim() : void
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
                'value' => new Request(),
                'expected' => ''
            )
        );
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->addFilterTrim()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filterString() : void
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
                'value' => new Request(),
                'expected' => ''
            )
        );
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->addStringFilter()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filterStripTags() : void
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
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->addStripTagsFilter()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    

    
    public function test_getBool() : void
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
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->getBool($name);
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_filter_commaSeparated() : void
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
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);

            $trim = $def['trim'] ?? true;
            $strip = $def['strip'] ?? true;

            $value = $request->registerParam($name)
            ->addCommaSeparatedFilter($trim, $strip)
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }



    public function test_getAcceptHeaders() : void
    {
        $tests = array(
            array(
                'label' => 'Chrome accept string',
                'value' => implode(',', array( 
                    'text/html',
                    'application/xhtml+xml',
                    'application/xml;q=0.9',
                    'image/webp',
                    'image/apng',
                    '*/*;q=0.8',
                    'application/signed-exchange;v=b3;q=0.9'
                )),
                'expected' => array(
                    'application/xml',
                    'application/signed-exchange',
                    '*/*',
                    'text/html',
                    'application/xhtml+xml',
                    'image/webp',
                    'image/apng',
                )
            ),
        );
        
        foreach($tests as $test)
        {
            $_SERVER['HTTP_ACCEPT'] = $test['value'];
            
            $accept = new Request_AcceptHeaders();
            $mimes = $accept->getMimeStrings();
            
            $this->assertEquals($test['expected'], $mimes, $test['label']);
        }
    }
    
    public function test_validateJSON() : void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => '',
            ),
            array(
                'label' => 'Integer value',
                'value' => 50,
                'expected' => '',
            ),
            array(
                'label' => 'String integer value',
                'value' => '100',
                'expected' => '',
            ),
            array(
                'label' => 'Quoted string',
                'value' => '"foo"',
                'expected' => ''
            ),
            array(
                'label' => 'Valid JSON object',
                'value' => '{"foo":"bar"}',
                'expected' => '{"foo":"bar"}'
            ),
            array(
                'label' => 'Valid JSON array',
                'value' => '["foo","bar"]',
                'expected' => '["foo","bar"]'
            ),
            array(
                'label' => 'Trimming whitespace around the string',
                'value' => '     ["foo","bar"]     ',
                'expected' => '["foo","bar"]'
            )
        );
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->setJSON()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }
    
    public function test_validateJSONObject() : void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => '',
            ),
            array(
                'label' => 'Integer value',
                'value' => 50,
                'expected' => '',
            ),
            array(
                'label' => 'String integer value',
                'value' => '100',
                'expected' => '',
            ),
            array(
                'label' => 'Quoted string',
                'value' => '"foo"',
                'expected' => ''
            ),
            array(
                'label' => 'Valid JSON object',
                'value' => '{"foo":"bar"}',
                'expected' => '{"foo":"bar"}'
            ),
            array(
                'label' => 'Valid JSON array',
                'value' => '["foo","bar"]',
                'expected' => ''
            )
        );
        
        $request = new Request();
        
        foreach($tests as $def)
        {
            $name = $this->setUniqueParam($def['value']);
            
            $value = $request->registerParam($name)
            ->setJSONObject()
            ->get();
            
            $this->assertEquals($def['expected'], $value, $def['label']);
        }
    }

    public function test_buildRefreshURL() : void
    {
        $_REQUEST = array(
            'foo' => 'bar',
            'exclude' => 'me',
            'option' => 'here',
            'other' => 'value'
        );

        $result = Request::getInstance()
            ->buildRefreshURL(
                array('foo' => 'no-bar'),
                array('exclude')
            );

        $this->assertSame(
            '/?foo=no-bar&amp;option=here&amp;other=value',
            $result
        );
    }

    public function test_buildURLSortsParameters() : void
    {
        $this->assertSame(
            '/?a=1&amp;b=2&amp;c=3',
            Request::getInstance()->buildURL(array(
                'c' => 3,
                'a' => 1,
                'b' => 2
            ))
        );
    }

    public function test_setDispatcher() : void
    {
        $this->assertSame(
            '/index.php?foo=bar',
            Request::getInstance()->buildURL(array('foo' => 'bar'), 'index.php')
        );
    }

    public function test_setDispatcherWithBaseURLAndNoDispatcher() : void
    {
        $request = Request::getInstance();

        $request->setBaseURL('https://domain.com/');

        $this->assertSame(
            'https://domain.com/?foo=bar',
            $request->buildURL(array('foo' => 'bar'))
        );
    }

    public function test_setDispatcherWithBaseURLAndDispatcher() : void
    {
        $request = Request::getInstance();

        $request->setBaseURL('https://domain.com/');

        $this->assertSame(
            'https://domain.com/index.php?foo=bar',
            $request->buildURL(array('foo' => 'bar'), 'index.php')
        );
    }

    public function test_setDispatcherNormalizeSlashes() : void
    {
        $request = Request::getInstance();

        $request->setBaseURL('https://domain.com//');

        $this->assertSame(
            'https://domain.com/index.php?foo=bar',
            $request->buildURL(array('foo' => 'bar'), '/index.php')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        Request::getInstance()->setBaseURL('');
    }

    // endregion
}
