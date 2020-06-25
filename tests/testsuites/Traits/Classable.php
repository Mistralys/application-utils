<?php

use PHPUnit\Framework\TestCase;

final class Traits_ClassableTest extends TestCase
{
   /**
    * @var \TraitClassable
    */
    protected $subject;
    
    protected function setUp() : void
    {
        $this->subject = new TraitClassable();
    }
    
    public function test_addClass()
    {
        $this->subject->addClass('foo');
        
        $this->assertEquals(array('foo'), $this->subject->getClasses());
    }
    
    public function test_addClasses()
    {
        $this->subject->addClasses(array('foo', 'bar'));
        
        $this->assertEquals(array('foo', 'bar'), $this->subject->getClasses());
    }
    
    public function test_removeClass()
    {
        $this->subject->addClasses(array('foo', 'bar'));
        
        $this->subject->removeClass('foo');
        
        $this->assertEquals(array('bar'), $this->subject->getClasses());
    }
    
    public function test_hasClass()
    {
        $this->subject->addClass('foo');
        
        $this->assertTrue($this->subject->hasClass('foo'));
    }
    
    public function test_classesToString()
    {
        $this->subject->addClasses(array('foo', 'bar'));
        
        $this->assertEquals('foo bar', $this->subject->classesToString());
    }
    
    public function test_classesToAttribute()
    {
        $this->subject->addClasses(array('foo', 'bar'));
        
        $this->assertEquals(' class="foo bar" ', $this->subject->classesToAttribute());
    }
}
