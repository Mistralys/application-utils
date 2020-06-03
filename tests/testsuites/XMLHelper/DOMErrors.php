<?php

use PHPUnit\Framework\TestCase;
use AppUtils\XMLHelper_DOMErrors;
use AppUtils\XMLHelper_DOMErrors_Error;
use AppUtils\XMLHelper_HTMLLoader;

final class XMLHelper_DOMErrorsTest extends TestCase
{
    public function test_valid()
    {
        $html = '<p></p>';
        
        $errors = XMLHelper_HTMLLoader::loadFragment($html)->getErrors();
        
        $this->assertTrue($errors->isValid());
    }
    
    public function test_nestingError()
    {
        $html = '<p><span></u></p>';
        
        $errors = XMLHelper_HTMLLoader::loadFragment($html)->getErrors();
        
        $this->assertFalse($errors->isValid(), 'Nesting error should not be valid.');
        $this->assertTrue($errors->hasNestingErrors(), 'Should have nesting errors.');
        $this->assertFalse($errors->hasFatalErrors());
        $this->assertTrue($errors->hasErrors());
        $this->assertFalse($errors->hasWarnings());
    }
    
    public function test_unknownTagError()
    {
        $html = '<p><unknown/></p>';
        
        $errors = XMLHelper_HTMLLoader::loadFragment($html)->getErrors();
        
        $this->assertFalse($errors->isValid(), 'Unknown tag should not be valid.');
        $this->assertTrue($errors->hasUnknownTags(), 'Should have unknown tag errors.');
    }
    
   /**
    * Ensures that no information is lost when serializing and unserializing.
    */
    public function test_serializeError()
    {
        $html = '<p><span></u></p>';
        
        $errors = XMLHelper_HTMLLoader::loadFragment($html)->getErrors()->getAll();
        $error = $errors[0];
        
        $serialized = $error->serialize();
        
        $unserialized = XMLHelper_DOMErrors_Error::fromSerialized($serialized);
        
        $this->assertEquals($serialized, $unserialized->serialize());
    }

    public function test_serializeErrors()
    {
        $html = '<p><span></u></p>';
        
        $errors = XMLHelper_HTMLLoader::loadFragment($html)->getErrors();
        
        $serialized = $errors->serialize();
        
        $unserialized = XMLHelper_DOMErrors::fromSerialized($serialized);
        
        $this->assertEquals($serialized, $unserialized->serialize());
    }
}
