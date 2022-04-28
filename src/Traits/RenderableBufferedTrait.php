<?php
/**
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Traits\RenderableBufferedTrait
 */

declare(strict_types=1);

namespace AppUtils\Traits;

use AppUtils\Interfaces\RenderableInterface;
use AppUtils\OutputBuffering;
use Throwable;

/**
 * Like the renderable trait, but uses output buffering
 * to get the rendered content. The method {@see RenderableBufferedTrait::generateOutput()}
 * is used to generate the output to use.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RenderableInterface
 */
trait RenderableBufferedTrait
{
    public function render() : string
    {
        OutputBuffering::start();

        $this->generateOutput();

        return OutputBuffering::get();
    }

    abstract protected function generateOutput() : void;

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
