<?php

use PHPUnit\Framework\TestCase;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

final class FileHelper_PHPClassInfoTest extends TestCase
{
    protected $assetsFolder;
    
    protected function setUp() : void
    {
        if(isset($this->assetsFolder)) {
            return;
        }
        
        $this->assetsFolder = realpath(TESTS_ROOT.'/assets/FileHelper/PHPClassInfo');
        
        if($this->assetsFolder === false) {
            throw new Exception(
                'The file helper assets folder could not be found.'
            );
        }
    }
    
    public function test_fileNotExists()
    {
        $this->expectException(FileHelper_Exception::class);
        
        $info = FileHelper::findPHPClasses('/path/to/unknown/file.php');
    }
    
    public function test_getInfo()
    {
        $tests = array(
            array(
                'label' => 'A single class',
                'file' => 'single-class',
                'classes' => array(
                    'SingleClass' => array(
                        'extends' => '',
                        'implements' => array(),
                        'declaration' => 'class SingleClass'
                    )
                ),
            ),
            array(
                'label' => 'A single class, extends',
                'file' => 'single-class-extended',
                'classes' => array(
                    'SingleClassExtended' => array(
                        'extends' => 'FooClass',
                        'implements' => array(),
                        'declaration' => 'class SingleClassExtended extends FooClass'
                    )
                ),
            ),
            array(
                'label' => 'A single class, implements',
                'file' => 'single-class-implements',
                'classes' => array(
                    'SingleClassImplements' => array(
                        'extends' => '',
                        'implements' => array('Foo1Interface'),
                        'declaration' => 'class SingleClassImplements implements Foo1Interface' 
                    )
                ),
            ),
            array(
                'label' => 'A single class, extends and implements',
                'file' => 'single-class-multiple',
                'classes' => array(
                    'SingleClassMultiple' => array(
                        'extends' => 'FooClass',
                        'implements' => array('Foo1Interface', 'Foo2Interface', 'Foo3Interface'),
                        'declaration' => 'class SingleClassMultiple extends FooClass implements Foo1Interface, Foo2Interface, Foo3Interface'
                    )
                ),
            ),
            array(
                'label' => 'A single class, multi with free spacing',
                'file' => 'single-class-multiple-freespacing',
                'classes' => array(
                    'SingleClassMultipleFreespacing' => array(
                        'extends' => 'FooClass',
                        'implements' => array('Foo1Interface', 'Foo2Interface', 'Foo3Interface'),
                        'declaration' => 'class SingleClassMultipleFreespacing extends FooClass implements Foo1Interface, Foo2Interface, Foo3Interface'
                    )
                ),
            )
        );
        
        foreach($tests as $test)
        {
            $info = FileHelper::findPHPClasses($this->assetsFolder.'/'.$test['file'].'.php');

            $names = $info->getClassNames();
            $testNames = array_keys($test['classes']);
            
            sort($names); sort($testNames);
            
            $this->assertEquals(
                $testNames, 
                $names, 
                $test['label'].': The class names should match.'
            );
            
            $classes = $info->getClasses();
            
            $this->assertEquals(
                count($test['classes']), 
                count($classes), 
                $test['label'].': The amount of classes should match.'
            );

            foreach($classes as $class) 
            {
                $name = $class->getName();
                
                $this->assertTrue(isset($test['classes'][$name]), 'The name should exist in the array.');
                
                $def = $test['classes'][$name];
                
                $this->assertEquals($def['extends'], $class->getExtends(), $test['label']);
                $this->assertEquals($def['implements'], $class->getImplements(), $test['label']);
                $this->assertEquals($def['declaration'], $class->getDeclaration(), $test['label']);
            }
        }
    }
}
