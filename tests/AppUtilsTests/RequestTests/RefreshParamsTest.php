<?php

declare(strict_types=1);

namespace AppUtilsTests\RequestTests;

use AppUtils\Request;
use TestClasses\BaseTestCase;

final class RefreshParamsTest extends BaseTestCase
{
    private Request $request;
    
    protected function setUp() : void
    {
        parent::setUp();

        $this->request = new Request();
    }
    
    protected function tearDown() : void
    {
        parent::tearDown();

        unset($this->request);
    }
    
    public function test_default() : void
    {
        $_REQUEST[session_name()] = 'foo';
        $_REQUEST['othervar'] = 'bar';
        $_REQUEST['_qf__1234'] = 'quickformvar';
        
        $params = $this->request->createRefreshParams();
        
        $this->assertEquals(
            array('othervar' => 'bar'), 
            $params->getParams()
        );
    }
    
    public function test_disable_autoExcludes() : void
    {
        $sessionName = session_name();

        $_REQUEST[$sessionName] = 'foo';
        $_REQUEST['_qf__1234'] = 'quickformvar';
        
        $params = $this->request->createRefreshParams()
        ->setExcludeQuickform(false)
        ->setExcludeSessionName(false);
        
        $this->assertEquals(
            array(
                $sessionName => 'foo',
                '_qf__1234' => 'quickformvar'
            ),
            $params->getParams()
        );
    }
    
    public function test_override() : void
    {
        $_REQUEST['myvar'] = 'foo';
        
        $params = $this->request->createRefreshParams()
        ->overrideParam('myvar', 'bar')
        ->overrideParam('bar', 'foo');
        
        $this->assertEquals(
            array(
                'myvar' => 'bar',
                'bar' => 'foo'
            ), 
            $params->getParams()
        );
    }
    
    public function test_override_excluded() : void
    {
        $name = session_name();
        
        $_REQUEST[$name] = 'foo';
        
        $params = $this->request->createRefreshParams()
        ->overrideParam($name, 'myval');
        
        $this->assertEquals(
            array($name => 'myval'), 
            $params->getParams()
        );
    }
    
    public function test_override_callback() : void
    {
        $_REQUEST['foobar'] = 'nope';
        
        $params = $this->request->createRefreshParams()
        ->excludeParamByCallback(function($paramName, $paramValue) 
        {
            return $paramValue === 'nope';
        });
        
        $this->assertEquals(
            array(),
            $params->getParams()
        );
    }
    
    public function test_override_callback_notBoolean() : void
    {
        $_REQUEST['foobar'] = 'nope';
        
        $params = $this->request->createRefreshParams()
        ->excludeParamByCallback(function($paramName, $paramValue) 
        {
            if($paramValue === 'nope')
            {
                // not a boolean true, so should not work
                return 1;
            }

            return -1;
        });
            
        $this->assertEquals(
            array('foobar' => 'nope'),
            $params->getParams()
        );
    }
    
    public function test_exclude_severalByName() : void
    {
        $_REQUEST['foo'] = 'bar';
        $_REQUEST['bar'] = 'foo';
        
        $params = $this->request->createRefreshParams()
        ->excludeParamsByName(array('foo', 'bar'));
        
        $this->assertEquals(
            array(),
            $params->getParams()
        );
    }
    
    public function test_override_multiple() : void
    {
        $_REQUEST['foo'] = 'bar';
        $_REQUEST['bar'] = 'foo';
        
        $params = $this->request->createRefreshParams()
        ->overrideParams(array(
            'foo' => 'foo',
            'bar' => 'bar'
        ));
        
        $this->assertEquals(
            array(
                'foo' => 'foo',
                'bar' => 'bar'
            ),
            $params->getParams()
        );
    }
    
   /**
    * Ensure that the internal conversion of 
    * key names to strings works as intended.
    */
    public function test_override_multipleNonStringKeys() : void
    {
        $_REQUEST['1'] = 'one';
        $_REQUEST['478'] = 'two';
        
        $params = $this->request->createRefreshParams()
        ->overrideParams(array(
            true => 'foo',
            478 => 'bar',
        ));
        
        $this->assertEquals(
            array(
                '1' => 'foo',
                '478' => 'bar'
            ),
            $params->getParams()
        );
    }
    
    public function test_apis_match() : void
    {
        $_REQUEST['foo'] = 'bar';
        $_REQUEST['bar'] = 'foo';
        $_REQUEST['lopos'] = 'vanilla';
        
        $objParams = $this->request->createRefreshParams()
        ->excludeParamByName('lopos')
        ->overrideParam('foo', 'foo')
        ->getParams();
        
        $funcParams = $this->request->getRefreshParams(array('foo' => 'foo'), array('lopos'));
        
        $this->assertEquals($objParams, $funcParams);
    }
}
