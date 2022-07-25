<?php
/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @see \AppUtils\ClassHelper
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\ClassHelper\ClassLoaderNotFoundException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use Composer\Autoload\ClassLoader;
use Throwable;

/**
 * Helper class to simplify working with dynamic class loading,
 * in a static analysis-tool-friendly way. PHPStan and co will
 * recognize the correct class types given class strings.
 *
 * @package Application Utils
 * @subpackage ClassFinder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClassHelper
{
    private static ?ClassLoader $classLoader = null;

    public const ERROR_CANNOT_RESOLVE_CLASS_NAME = 111001;
    public const ERROR_THROWABLE_GIVEN_AS_OBJECT = 111002;

    /**
     * Attempts to detect the name of a class, switching between
     * the older class naming scheme with underscores (Long_Class_Name)
     * and namespaces.
     *
     * @param string $legacyName
     * @param string $nsPrefix Optional namespace prefix, if the namespace contains
     *                         the vendor name, for example (Vendor\PackageName\Folder\Class).
     * @return string|null The detected class name, or NULL otherwise.
     */
    public static function resolveClassName(string $legacyName, string $nsPrefix='') : ?string
    {
        $names = array(
            str_replace('\\', '_', $legacyName),
            str_replace('_', '\\', $legacyName),
            $nsPrefix.'\\'.str_replace('_', '\\', $legacyName)
        );

        foreach($names as $name) {
            if (class_exists($name)) {
                return ltrim($name, '\\');
            }
        }

        return null;
    }

    /**
     * Like {@see ClassHelper::resolveClassName()}, but throws an exception
     * if the class can not be found.
     *
     * @param string $legacyName
     * @param string $nsPrefix Optional namespace prefix, if the namespace contains
     *                         the vendor name, for example (Vendor\PackageName\Folder\Class).
     * @return string
     * @throws ClassNotExistsException
     */
    public static function requireResolvedClass(string $legacyName, string $nsPrefix='') : string
    {
        $class = self::resolveClassName($legacyName, $nsPrefix);

        if($class !== null)
        {
            return $class;
        }

        throw new ClassNotExistsException(
            $legacyName,
            self::ERROR_CANNOT_RESOLVE_CLASS_NAME
        );
    }

    /**
     * Throws an exception if the target class can not be found.
     *
     * @param string $className
     * @return void
     * @throws ClassNotExistsException
     */
    public static function requireClassExists(string $className) : void
    {
        if(class_exists($className))
        {
            return;
        }

        throw new ClassNotExistsException($className);
    }

    /**
     * Requires the target class name to exist, and extend
     * or implement the specified class/interface. If it does
     * not, an exception is thrown.
     *
     * @param class-string $targetClass
     * @param class-string $extendsClass
     * @return void
     *
     * @throws ClassNotImplementsException
     * @throws ClassNotExistsException
     */
    public static function requireClassInstanceOf(string $targetClass, string $extendsClass) : void
    {
        self::requireClassExists($targetClass);
        self::requireClassExists($extendsClass);

        if(is_a($targetClass, $extendsClass, true))
        {
            return;
        }

        throw new ClassNotImplementsException($extendsClass, $targetClass);
    }

    /**
     * If the target object is not an instance of the target class
     * or interface, throws an exception.
     *
     * NOTE: If an exception is passed as object, a class helper
     * exception is thrown with the error code {@see ClassHelper::ERROR_THROWABLE_GIVEN_AS_OBJECT},
     * and the original exception as previous exception.
     *
     * @template ClassInstanceType
     * @param class-string<ClassInstanceType> $class
     * @param object $object
     * @param int $errorCode
     * @return ClassInstanceType
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public static function requireObjectInstanceOf(string $class, object $object, int $errorCode=0)
    {
        if($object instanceof Throwable)
        {
            throw new ClassNotExistsException(
                $class,
                self::ERROR_THROWABLE_GIVEN_AS_OBJECT,
                $object
            );
        }

        if(!class_exists($class) && !interface_exists($class) && !trait_exists($class))
        {
            throw new ClassNotExistsException($class, $errorCode);
        }

        if(is_a($object, $class, true))
        {
            return $object;
        }

        throw new ClassNotImplementsException($class, $object, $errorCode);
    }

    /**
     * Retrieves an instance of the Composer class loader of
     * the current project. This assumes the usual structure
     * with this library being stored in the `vendor` folder.
     *
     * NOTE: Also works when working on a local copy of the
     * Git package.
     *
     * @return ClassLoader
     * @throws ClassLoaderNotFoundException
     */
    public static function getClassLoader() : ClassLoader
    {
        if(isset(self::$classLoader)) {
            return self::$classLoader;
        }

        // Paths are either the folder structure when the
        // package has been installed as a dependency via
        // composer, or a local installation of the git package.
        $paths = array(
            __DIR__.'/../../../autoload.php',
            __DIR__.'/../vendor/autoload.php'
        );

        $autoloadFile = null;

        foreach($paths as $path)
        {
            if(file_exists($path)) {
                $autoloadFile = $path;
            }
        }

        if($autoloadFile === null) {
            throw new ClassLoaderNotFoundException($paths);
        }

        $loader = require $autoloadFile;

        if (!$loader instanceof ClassLoader)
        {
            throw new ClassLoaderNotFoundException($paths);
        }

        self::$classLoader = $loader;

        return self::$classLoader;
    }

    /**
     * Gets the last part in a class name, e.g.:
     *
     * - `Class_Name_With_Underscores` -> `Underscores`
     * - `Class\With\Namespace` -> `Namespace`
     *
     * @param class-string|string|object $subject
     * @return string
     */
    public static function getClassTypeName($subject) : string
    {
        $parts = self::splitClass($subject);
        return array_pop($parts);
    }

    /**
     * Retrieves the namespace part of a class name, if any.
     *
     * @param class-string|object $subject
     * @return string
     */
    public static function getClassNamespace($subject) : string
    {
        $parts = self::splitClass($subject);
        array_pop($parts);

        return ltrim(implode('\\', $parts), '\\');
    }

    /**
     * @param class-string|object $subject
     * @return string[]
     */
    private static function splitClass($subject) : array
    {
        if(is_object($subject)) {
            $class = get_class($subject);
        } else {
            $class = $subject;
        }

        $class = str_replace('\\', '_', $class);

        return explode('_', $class);
    }
}
