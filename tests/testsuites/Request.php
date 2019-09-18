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
}