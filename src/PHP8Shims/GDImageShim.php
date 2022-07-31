<?php
/**
 * @package AppUtils
 * @subpackage PHP8 Shims
 */

declare(strict_types=1);

if(!class_exists('GdImage'))
{
    /**
     * Shim to support PHPStan analysis on PHP7, while
     * implementing types for PHP8. The GD library works
     * with <code>GdImage</code> objects instead of
     * resources.
     *
     * The class is abstract, so it cannot be instantiated
     * by mistake.
     *
     * @package AppUtils
     * @subpackage PHP8 Shims
     * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
     */
    abstract class GdImage
    {

    }
}
