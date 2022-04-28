<?php
/**
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Traits\RenderableTrait
 */

declare(strict_types=1);

namespace AppUtils\Traits;

use AppUtils\Interfaces\RenderableInterface;
use Throwable;

/**
 * Trait used to quickly implement the interface
 * {@see RenderableInterface}: Only the `render()`
 * method needs to be implemented.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RenderableInterface
 */
trait RenderableTrait
{
    public function display() : void
    {
        echo $this->render();
    }

    public function __toString() : string
    {
        try
        {
            return $this->render();
        }
        catch (Throwable $e)
        {
            return sprintf(
                'Exception while rendering [%s]: %s',
                get_class($this),
                $e->getMessage()
            );
        }
    }
}
