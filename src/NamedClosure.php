<?php
/**
 * File containing the class {@see NamedClosure}.
 *
 * @package Application Utils
 * @subpackage Closures
 * @see NamedClosure
 */

declare(strict_types=1);

namespace AppUtils;

use Closure;

/**
 * Wrapper for closures, which allows specifying a descriptive
 * string to make it possible to easily identify it again later.
 *
 * Usage:
 *
 * <pre>
 * $closure = NamedClosure::fromClosure(
 *     function() {
 *         // do something
 *     },
 *     'Descriptive closure identifier'
 * );
 *
 * if($closure instanceof NamedClosure) {
 *     $origin = $closure->getOrigin();
 * }
 * </pre>
 *
 * @package Application Utils
 * @subpackage Closures
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class NamedClosure
{
    /**
     * @var Closure
     */
    private $closure;

    /**
     * @var string
     */
    private $origin;

    /**
     * NamedClosure constructor.
     * @param Closure $closure
     * @param string $origin
     */
    private function __construct(Closure $closure, string $origin)
    {
        $this->closure = $closure;
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function getOrigin() : string
    {
        return $this->origin;
    }

    /**
     * @param Closure $closure
     * @param object|string $origin
     * @return NamedClosure
     */
    public static function fromClosure(Closure $closure, $origin) : NamedClosure
    {
        if(is_object($origin)) {
            $origin = get_class($origin);
        }

        return new NamedClosure($closure, $origin);
    }

    /**
     * @param object $object
     * @param string $method
     * @param string|object $origin Optional origin. If not specified, the object and method name are used instead.
     * @return NamedClosure
     */
    public static function fromObject(object $object, string $method, $origin='') : NamedClosure
    {
        return self::fromArray(array($object, $method), $origin);
    }

    /**
     * @param array $callback
     * @param string|object $origin
     * @return NamedClosure
     */
    public static function fromArray(array $callback, $origin='') : NamedClosure
    {
        if(empty($origin)) {
            $origin = ConvertHelper::callback2string($callback);
        } else if(is_object($origin)) {
            $origin = get_class($origin);
        }

        return new NamedClosure(Closure::fromCallable($callback), $origin);
    }

    public function __invoke()
    {
        $args = func_get_args();
        $args[] = $this;

        return call_user_func($this->closure, ...$args);
    }
}
