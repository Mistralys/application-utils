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

    /**
     * Attempts to detect the name of a class, switching between
     * the older class naming scheme with underscores (Long_Class_Name)
     * and namespaces.
     *
     * @param string $legacyName
     * @return string|null The detected class name, or NULL otherwise.
     */
    public static function resolveClassName(string $legacyName) : ?string
    {
        // Handle cases where we have a mix of styles because of
        // get_class() used to build a class name.
        $legacyName = str_replace('\\', '_', $legacyName);

        if(class_exists($legacyName))
        {
            return $legacyName;
        }

        $nameNS = str_replace('_', '\\', $legacyName);

        if(class_exists($nameNS))
        {
            return ltrim($nameNS, '\\');
        }

        return null;
    }

    /**
     * Like {@see ClassHelper::resolveClassName()}, but throws an exception
     * if the class can not be found.
     *
     * @param string $legacyName
     * @return string
     * @throws ClassNotExistsException
     */
    public static function requireResolvedClass(string $legacyName) : string
    {
        $class = self::resolveClassName($legacyName);

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
}
