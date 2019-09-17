<?php

use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected $urls = array(
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
    );
    
    public function test_urlsMatch()
    {
        $request = new AppUtils\Request();
        
        foreach($this->urls as $def)
        {
            $limitParams = array();
            if(isset($def['limitParams'])) {
                $limitParams = $def['limitParams'];
            }
            
            $result = $request->urlsMatch(
                $def['sourceUrl'], 
                $def['targetUrl'],
                $limitParams
            );
            
            $this->assertEquals($def['match'], $result, $def['label']);
        }
    }
}