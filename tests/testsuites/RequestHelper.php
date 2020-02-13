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
    
   /**
    * Checks that sending a file works as intended.
    */
    public function test_sendFile()
    {
        if(!defined('TESTS_WEBSERVER_URL')) 
        {
            $this->markTestSkipped('The webserver URL has not been defined in the config file.');
            return;
        }
        
        $helper = new RequestHelper(TESTS_WEBSERVER_URL.'/assets/RequestHelper/PostCatcher.php');
        
        //$helper->enableLogging($this->assetsFolder.'/curl-log.txt');
        
        $originalContent = file_get_contents($this->assetsFolder.'/upload.html');
        
        $helper->addFile(
            'htmlfile', 
            'uploaded-file.html', 
            $originalContent
        );
        
        $json = $helper->send();
        
        $response = $helper->getResponse();
        
        $this->assertNotEmpty($json);
        $this->assertEquals(200, $response->getCode());
        
        $data = json_decode($json, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('files', $data);
        $this->assertArrayHasKey('htmlfile', $data['files']);
        $this->assertEquals(0, $data['files']['htmlfile']['error']);
        $this->assertEquals($originalContent, $data['files']['htmlfile']['content']);
    }
    
   /**
    * Checks that sending JSON keeps the JSON data intact, 
    * so that reading it back in it equals the source JSON.
    */
    public function test_sendJSON()
    {
        if(!defined('TESTS_WEBSERVER_URL'))
        {
            $this->markTestSkipped('The webserver URL has not been defined in the config file.');
            return;
        }
        
        $helper = new RequestHelper(TESTS_WEBSERVER_URL.'/assets/RequestHelper/PostCatcher.php');
        
        $originalJSON = json_encode(array(
            'key' => 'value',
            'foo' => 'öäü',
            'bar' => array(
                'number' => 0,
                'bool' => false
            )
        ));
        
        $helper->addContent(
            'arbitrary', 
            $originalJSON, 
            'application/json'
        );
        
        $json = $helper->send();
        
        $response = $helper->getResponse();
        
        $this->assertEquals(200, $response->getCode());
        $this->assertNotEmpty($json);
        
        $data = json_decode($json, true);
        
        $this->assertIsArray($data);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('request', $data);
        $this->assertArrayHasKey('arbitrary', $data['request']);
        $this->assertEquals($originalJSON, $data['request']['arbitrary']);
    }
}
