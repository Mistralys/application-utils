<?php

declare(strict_types=1);

namespace testsuites;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use stdClass;
use TestClasses\ClassHelper\InstanceOfClass;
use TestClasses\ClassHelper\Namespaced\ExampleClass;
use TestClasses_ClassHelper_LegacyNaming_ExampleClass;
use PHPUnit\Framework\TestCase;

final class ClassHelperTests extends TestCase
{
    public function test_getAutoLoader() : void
    {
        ClassHelper::getClassLoader();

        // No exception = all good
        $this->addToAssertionCount(1);
    }

    public function test_resolveClassName() : void
    {
        $this->assertTrue(class_exists(TestClasses_ClassHelper_LegacyNaming_ExampleClass::class));
        $this->assertTrue(class_exists(ExampleClass::class));

        $this->assertSame(
            TestClasses_ClassHelper_LegacyNaming_ExampleClass::class,
            ClassHelper::resolveClassName('TestClasses_ClassHelper_LegacyNaming_ExampleClass')
        );

        $this->assertSame(
            ExampleClass::class,
            ClassHelper::resolveClassName('TestClasses\ClassHelper\Namespaced\ExampleClass')
        );

        $this->assertSame(
            ExampleClass::class,
            ClassHelper::resolveClassName('\TestClasses\ClassHelper\Namespaced\ExampleClass')
        );

        $this->assertSame(
            stdClass::class,
            ClassHelper::resolveClassName('\stdClass')
        );

        $this->assertSame(
            BaseClassHelperException::class,
            ClassHelper::resolveClassName('ClassHelper\BaseClassHelperException', 'AppUtils')
        );
    }

    public function test_requireResolvedClass() : void
    {
        $this->expectExceptionCode(ClassHelper::ERROR_CANNOT_RESOLVE_CLASS_NAME);

        ClassHelper::requireResolvedClass('Unknown_Class_Name');
    }

    public function test_requireClassExists() : void
    {
        ClassHelper::requireClassExists(ExampleClass::class);

        $this->addToAssertionCount(1);
    }

    public function test_requireClassExistsException() : void
    {
        $this->expectException(ClassNotExistsException::class);

        ClassHelper::requireClassExists('Unknown_Class_Name');
    }

    public function test_requireClassInstanceOf() : void
    {
        ClassHelper::requireClassInstanceOf(
            InstanceOfClass::class,
            ExampleClass::class
        );

        $this->addToAssertionCount(1);
    }

    public function test_requireClassInstanceOfException() : void
    {
        $this->expectException(ClassNotImplementsException::class);

        ClassHelper::requireClassInstanceOf(
            ExampleClass::class,
            InstanceOfClass::class
        );
    }

    public function test_requireObjectInstanceOf() : void
    {
        ClassHelper::requireObjectInstanceOf(
            ExampleClass::class,
            new InstanceOfClass()
        );

        $this->addToAssertionCount(1);
    }

    public function test_requireObjectInstanceOfException() : void
    {
        $this->expectException(ClassNotImplementsException::class);

        ClassHelper::requireObjectInstanceOf(
            InstanceOfClass::class,
            new ExampleClass()
        );
    }

    public function test_getClassTypeName() : void
    {
        $this->assertSame(
            'Namespace',
            ClassHelper::getClassTypeName('Class\With\Namespace')
        );

        $this->assertSame(
            'Underscores',
            ClassHelper::getClassTypeName('Class_Name_With_Underscores')
        );

        $this->assertSame(
            'ClassName',
            ClassHelper::getClassTypeName('ClassName')
        );
    }

    public function test_getClassNamespace() : void
    {
        $this->assertSame(
            '',
            ClassHelper::getClassNamespace('ClassName')
        );

        $this->assertSame(
            'AppUtils',
            ClassHelper::getClassNamespace('AppUtils\ClassName')
        );

        $this->assertSame(
            'AppUtils',
            ClassHelper::getClassNamespace('\AppUtils\ClassName')
        );

        $this->assertSame(
            'Mistralys\AppUtils',
            ClassHelper::getClassNamespace('Mistralys\AppUtils\ClassName')
        );
    }
}
