<?php

declare(strict_types=1);

namespace testsuites\FileHelperTests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

final class PHPClassInfoTest extends TestCase
{
    protected $assetsFolder;
    
    protected function setUp() : void
    {
        if(isset($this->assetsFolder)) {
            return;
        }
        
        $this->assetsFolder = realpath(TESTS_ROOT.'/assets/FileHelper/PHPClassInfo');
        
        if($this->assetsFolder === false) {
            throw new InvalidArgumentException(
                'The file helper assets folder could not be found.'
            );
        }
    }
    
    public function test_fileNotExists() : void
    {
        $this->expectException(FileHelper_Exception::class);
        
        FileHelper::findPHPClasses('/path/to/unknown/file.php');
    }
    
    public function test_getInfo() : void
    {
        $tests = array(
            array(
                'label' => 'No classes in file',
                'file' => 'no-classes',
                'classes' => array(),
            ),
            array(
                'label' => 'A single class',
                'file' => 'single-class',
                'classes' => array(
                    'SingleClass' => array(
                        'name' => 'SingleClass',
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
                        'name' => 'SingleClassExtended',
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
                        'name' => 'SingleClassImplements',
                        'extends' => '',
                        'implements' => array('Foo1Interface'),
                        'declaration' => 'class SingleClassImplements implements Foo1Interface' 
                    )
                ),
            ),
            array(
                'label' => 'A single class, namespaced',
                'file' => 'single-class-namespaced',
                'classes' => array(
                    'SingleClassNamespaced\SingleClass' => array(
                        'name' => 'SingleClass',
                        'extends' => '',
                        'implements' => array(),
                        'declaration' => 'class SingleClass'
                    )
                ),
            ),
            array(
                'label' => 'A single class, extends and implements',
                'file' => 'single-class-multiple',
                'classes' => array(
                    'SingleClassMultiple' => array(
                        'name' => 'SingleClassMultiple',
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
                        'name' => 'SingleClassMultipleFreespacing',
                        'extends' => 'FooClass',
                        'implements' => array('Foo1Interface', 'Foo2Interface', 'Foo3Interface'),
                        'declaration' => 'class SingleClassMultipleFreespacing extends FooClass implements Foo1Interface, Foo2Interface, Foo3Interface'
                    )
                ),
            ),
            array(
                'label' => 'A trait',
                'file' => 'trait',
                'classes' => array(
                    'SupahTrait' => array(
                        'name' => 'SupahTrait',
                        'extends' => '',
                        'implements' => array(),
                        'declaration' => 'trait SupahTrait'
                    )
                ),
            ),
            array(
                'label' => 'Class names in comments',
                'file' => 'comment-class',
                'classes' => array(),
            ),
            array(
                'label' => 'Multiple classes',
                'file' => 'multi-class',
                'classes' => array(
                    'MultiClassOne' => array(
                        'name' => 'MultiClassOne',
                        'extends' => '',
                        'implements' => array(),
                        'declaration' => 'class MultiClassOne'
                    ),
                    'MultiClassTwo' => array(
                        'name' => 'MultiClassTwo',
                        'extends' => 'FooClass',
                        'implements' => array(),
                        'declaration' => 'class MultiClassTwo extends FooClass'
                    ),
                    'MultiClassThree' => array(
                        'name' => 'MultiClassThree',
                        'extends' => 'MultiClassOne',
                        'implements' => array('Foo1Interface', 'Foo2Interface'),
                        'declaration' => 'class MultiClassThree extends MultiClassOne implements Foo1Interface, Foo2Interface'
                    )
                ),
            )
        );
        
        foreach($tests as $test)
        {
            $this->getInfo_checkTest($test);
        }
    }

    /**
     * @param array{label:string,file:string,classes:array<string,array{name:string,extends:string,implements:array<int,string>,declaration:string}>} $test
     * @return void
     */
    private function getInfo_checkTest(array $test) : void
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

        $this->assertCount(
            count($test['classes']),
            $classes,
            $test['label'] . ': The amount of classes should match.'
        );

        foreach($classes as $class)
        {
            $name = $class->getNameNS();

            $this->assertTrue(isset($test['classes'][$name]), 'The class name ['.$name.'] should exist in the array.');

            $def = $test['classes'][$name];

            $this->assertEquals($def['name'], $class->getName(), $test['label']);
            $this->assertEquals($def['extends'], $class->getExtends(), $test['label']);
            $this->assertEquals($def['implements'], $class->getImplements(), $test['label']);
            $this->assertEquals($def['declaration'], $class->getDeclaration(), $test['label']);
        }
    }
}
