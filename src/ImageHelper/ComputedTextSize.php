<?php

declare(strict_types=1);

namespace AppUtils\ImageHelper;

use AppUtils\ImageHelper_Size;
use ArrayAccess;


/**
 * @package AppUtils
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @implements ArrayAccess<string,int|float>
 */
class ComputedTextSize implements ArrayAccess
{
    /**
     * @var array{size:float,top_left_x:int,top_left_y:int,top_right_x:int,top_right_y:int,bottom_left_x:int,bottom_left_y:int,bottom_right_x:int,bottom_right_y:int,width:int,height:int}
     */
    private array $size;

    /**
     * @param array{size:float,top_left_x:int,top_left_y:int,top_right_x:int,top_right_y:int,bottom_left_x:int,bottom_left_y:int,bottom_right_x:int,bottom_right_y:int,width:int,height:int} $sizeArray
     */
    public function __construct(array $sizeArray)
    {
        $this->size = $sizeArray;
    }

    public function getWidth() : int
    {
        return $this->size['width'];
    }

    public function getHeight() : int
    {
        return $this->size['height'];
    }

    /**
     * @return float
     */
    public function getFontSize() : float
    {
        return $this->size['size'];
    }

    public function getLeft() : int
    {
        return $this->size['top_left_x'];
    }

    public function getRight() : int
    {
        return $this->size['top_right_x'];
    }

    public function getTop() : int
    {
        return $this->size['top_left_y'];
    }

    public function getBottom() : int
    {
        return $this->size['bottom_left_y'];
    }

    public function getSize() : ImageHelper_Size
    {
        return new ImageHelper_Size(array(
            $this->size['width'],
            $this->size['height'])
        );
    }

    public function getTopLeft() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->size['top_left_x'],
            $this->size['top_left_y']
        );
    }

    public function getTopRight() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->size['top_right_x'],
            $this->size['top_right_y']
        );
    }

    public function getBottomLeft() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->size['bottom_left_x'],
            $this->size['bottom_left_y']
        );
    }

    public function getBottomRight() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->size['bottom_right_x'],
            $this->size['bottom_right_y']
        );
    }

    // region: ArrayAccess interface

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->size[$offset]);
    }

    /**
     * @param string $offset
     * @return int|float
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->size[$offset] ?? -1;
    }

    /**
     * @param string $offset
     * @param int|float $value
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        $this->size[$offset] = $value;
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        // we do not want to unset any of the keys.
    }

    // endregion
}
