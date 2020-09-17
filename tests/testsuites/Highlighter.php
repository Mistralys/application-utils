<?php

use PHPUnit\Framework\TestCase;
use AppUtils\Highlighter;

final class HighlighterTest extends TestCase
{
   /**
    * @var string
    */
    private $assetsFolder;
    
   /**
    * @var string
    */
    private $exampleString = '<p>Foobar</p>';

   /**
    * @var string
    */
    private $exampleOutput =
    '<pre class="html" style="font-family:monospace;">'.
        '&lt;p&gt;Foobar&lt;/p&gt;'.
    '</pre>';
    
    protected function setUp() : void
    {
        $this->assetsFolder = realpath(TESTS_ROOT.'/assets/Highlighter');
        
        if($this->assetsFolder === false) 
        {
            throw new Exception(
                'The highlighter\'s assets folder could not be found.'
            );
        }
    }
    
    public function test_fromString() : void
    {
        $instance = Highlighter::fromString($this->exampleString, 'html');
        
        $this->assertInstanceOf(GeSHi::class, $instance);
    }
    
    public function test_fromFile() : void
    {
        $instance = Highlighter::fromFile($this->assetsFolder.'/example.html', 'html');
        
        $this->assertInstanceOf(GeSHi::class, $instance);
    }
    
    public function test_parseString() : void
    {
        $result = Highlighter::parseString($this->exampleString, 'html');
        
        $this->assertEquals($this->exampleOutput, $result);
    }
    
    public function test_parseFile() : void
    {
        $result = Highlighter::parseFile($this->assetsFolder.'/example.html', 'html');
        
        $this->assertEquals($this->exampleOutput, $result);
    }
}
