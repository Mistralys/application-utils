<?php

use PHPUnit\Framework\TestCase;
use AppUtils\RequestHelper;
use AppUtils\RequestHelper_Exception;

final class RequestHelperTest extends TestCase
{
    protected $assetsFolder = '';
    
    protected function setUp() : void
    {
        if(empty($this->assetsFolder))
        {
            $this->assetsFolder = TESTS_ROOT.'/assets/RequestHelper';
        }
    }
    
    public function test_sendEmpty()
    {
        $helper = new RequestHelper('http://www.foo.nowhere');
        
        $this->expectException(RequestHelper_Exception::class);
        
        $helper->send();
    }
    
    public function test_sendFile()
    {
        if(!defined('TESTS_WEBSERVER_URL')) 
        {
            $this->markTestSkipped('The webserver URL has not been defined in the config file.');
            return;
        }
        
        $helper = new RequestHelper(TESTS_WEBSERVER_URL.'/assets/RequestHelper/PostCatcher.php');
        
        //$helper->enableLogging($this->assetsFolder.'/curl-log.txt');
        
        $helper->addFile(
            'htmlfile', 
            'uploaded-file.html', 
            file_get_contents($this->assetsFolder.'/upload.html')
        );
        
        $json = $helper->send();
        
        $this->assertNotEmpty($json);
        
        $response = $helper->getResponse();
        
        $this->assertEquals(200, $response->getCode());
        
        $data = json_decode($json, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('files', $data);
        $this->assertArrayHasKey('htmlfile', $data['files']);
        $this->assertEquals(0, $data['files']['htmlfile']['error']);
    }
}
