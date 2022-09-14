<?php

declare(strict_types=1);

namespace RequestTests;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\RequestHelper;
use AppUtils\RequestHelper_Exception;
use TestClasses\BaseTestCase;

final class RequestHelperTest extends BaseTestCase
{
    protected string $assetsFolder = '';
    
    protected function setUp() : void
    {
        if(empty($this->assetsFolder))
        {
            $this->assetsFolder = TESTS_ROOT.'/assets/RequestHelper';
        }
    }
    
    public function test_sendEmpty() : void
    {
        $helper = new RequestHelper('http://www.foo.nowhere');
        
        $this->expectException(RequestHelper_Exception::class);
        
        $helper->send();
    }

   /**
    * Checks that sending a file works as intended.
    */
    public function test_sendFile() : void
    {
        $this->skipWebserverURL();
        
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
        
        $data = JSONConverter::json2array($json);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('files', $data);
        $this->assertArrayHasKey('htmlfile', $data['files']);
        $this->assertEquals(0, $data['files']['htmlfile']['error']);
        $this->assertEquals($originalContent, $data['files']['htmlfile']['content']);
    }
    
    public function test_sendTextfile() : void
    {
        $this->skipWebserverURL();

        $helper = new RequestHelper(TESTS_WEBSERVER_URL.'/assets/RequestHelper/PostCatcher.php');
        
        $helper->enableLogging($this->assetsFolder.'/curl-log.txt');
        
        $originalContent = file_get_contents($this->assetsFolder.'/upload.txt');
        
        $helper->addFile(
            'textfile',
            'uploaded-textfile.txt',
            $originalContent
        );
        
        $json = $helper->send();
        
        $response = $helper->getResponse();
        
        $this->assertNotEmpty($json);
        $this->assertEquals(200, $response->getCode());
        
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('files', $data);
        $this->assertArrayHasKey('textfile', $data['files']);
        $this->assertEquals(0, $data['files']['textfile']['error']);
        $this->assertEquals($originalContent, $data['files']['textfile']['content']);
    }
    
   /**
    * Checks that sending JSON keeps the JSON data intact, 
    * so that reading it back in it equals the source JSON.
    */
    public function test_sendJSON() : void
    {
        $this->skipWebserverURL();
        
        $helper = new RequestHelper(TESTS_WEBSERVER_URL.'/assets/RequestHelper/PostCatcher.php');
        
        $originalJSON = json_encode(array(
            'key' => 'value',
            'foo' => 'öäü',
            'bar' => array(
                'number' => 0,
                'bool' => false
            )
        ), JSON_THROW_ON_ERROR);
        
        $helper->addContent(
            'arbitrary', 
            $originalJSON, 
            'application/json'
        );
        
        $json = $helper->send();
        
        $response = $helper->getResponse();
        
        $this->assertEquals(200, $response->getCode());
        $this->assertNotEmpty($json);
        
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        
        $this->assertIsArray($data);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('request', $data);
        $this->assertArrayHasKey('arbitrary', $data['request']);
        $this->assertEquals($originalJSON, $data['request']['arbitrary']);
    }
}
