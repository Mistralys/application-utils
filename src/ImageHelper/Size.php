<?php
/**
 * @package Application Utils
 * @subpackage ImageHelper
 * @see \AppUtils\ImageHelper_Size
 */

declare(strict_types=1);

namespace AppUtils;

use ArrayAccess;
use function AppUtils\RGBAColor\imgSize;
use function PHPUnit\Framework\lessThanOrEqual;

/**
 * Size container: instances of this class are returned when
 * using the {@link ImageHelper::getImageSize()} method, to
 * easily access the size information.
 *
 * @package Application Utils
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string|int,int>
 *
 * @see ImageHelper::getImageSize()
 * @see imgSize()
 */
class ImageHelper_Size implements ArrayAccess
{
    public const RESIZE_MODE_WIDTH = 'width';
    public const RESIZE_MODE_HEIGHT = 'height';

    // region: C - Instantiating

    /**
     * @var array{width:int,height:int,channels:int,bits:int,0:int,1:int}
     */
    protected array $size;

    /**
     * @param array<string|int,int> $size
     */
    public function __construct(array $size)
    {
        $this->size = array();

        $this->size['width'] = $size['width'] ?? $size[0] ?? 0;
        $this->size['height'] = $size['height'] ?? $size[1] ?? 0;
        $this->size[0] = $this->size['width'];
        $this->size[1] = $this->size['height'];
        $this->size['channels'] = $size['channels'] ?? 1;
        $this->size['bits'] = $size['bits'] ?? -1;
    }

    /**
     * @param int|array<string|int,int>|ImageHelper_Size $widthArrayOrInstance
     * @return ImageHelper_Size
     */
    public static function create($widthArrayOrInstance, ?int $height=null) : ImageHelper_Size
    {
        if($widthArrayOrInstance instanceof self) {
            return new self($widthArrayOrInstance->toArray());
        }

        if(is_int($widthArrayOrInstance) && is_int($height)) {
            return new self(array($widthArrayOrInstance, $height));
        }

        return new self($widthArrayOrInstance);
    }

    // endregion

    // region: A - Accessing values

    public function getWidth() : int
    {
        return $this->size['width'];
    }
    
    public function getHeight() : int
    {
        return $this->size['height'];
    }
    
    public function getChannels() : int
    {
        return $this->size['channels'];
    }
    
    public function getBits() : int
    {
        return $this->size['bits'];
    }

    /**
     * @return array{width:int,height:int,channels:int,bits:int,0:int,1:int}
     */
    public function toArray() : array
    {
        return $this->size;
    }

    public function toReadableString(bool $includeBitsAndChannels=false) : string
    {
        if($includeBitsAndChannels) {
            return t(
                '%1$s x %2$s px %3$s bits, %4$s channels',
                $this->getWidth(),
                $this->getHeight(),
                $this->getBits(),
                $this->getChannels()
            );
        }

        return t(
            '%1$s x %2$s px',
            $this->getWidth(),
            $this->getHeight()
        );
    }

    /**
     * The width/height ratio of the size.
     * @return float
     */
    public function getRatio() : float
    {
        return $this->getWidth() / $this->getHeight();
    }

    public function isSquare() : bool
    {
        return $this->getWidth() === $this->getHeight();
    }

    public function isLandscape() : bool
    {
        return $this->getWidth() > $this->getHeight();
    }

    public function isPortrait() : bool
    {
        return $this->getWidth() < $this->getHeight();
    }

    // endregion

    // region: B - Resizing

    /**
     * Resizes to the target width, maintaining aspect ratio.
     * @param int $width
     * @return ImageHelper_Size (New instance)
     */
    public function resizeByWidth(int $width) : ImageHelper_Size
    {
        return imgSize(
            $width,
            (int)floor($width / $this->getRatio())
        );
    }

    /**
     * Resizes to the target height, maintaining aspect ratio.
     * @param int $height
     * @return ImageHelper_Size (New instance)
     */
    public function resizeByHeight(int $height) : ImageHelper_Size
    {
        return imgSize(
            (int)floor($height * $this->getRatio()),
            $height
        );
    }

    /**
     * Given the orientation of the size (portrait/landscape),
     * get the resize mode to use to maintain aspect ratio.
     *
     * @return string
     * @see self::RESIZE_MODE_WIDTH
     * @see self::RESIZE_MODE_HEIGHT
     */
    public function getResizeMode() : string
    {
        if($this->isLandscape()) {
            return self::RESIZE_MODE_WIDTH;
        }

        return self::RESIZE_MODE_HEIGHT;
    }

    /**
     * Resizes down to fit into the target size - if the target size
     * is smaller - while maintaining aspect ratio.
     *
     * @param ImageHelper_Size $size
     * @param bool $scaleUpAllowed Whether it is allowed to scale upwards if the target size is bigger.
     * @return ImageHelper_Size
     */
    public function resizeInto(ImageHelper_Size $size, bool $scaleUpAllowed=false) : ImageHelper_Size
    {
        if($size->isPortrait())
        {
            if($this->isPortrait()) {
                $mode = self::RESIZE_MODE_HEIGHT;
            } else {
                $mode = self::RESIZE_MODE_WIDTH;
            }
        }
        else if($size->isLandscape())
        {
            if($this->isLandscape()) {
                $mode = self::RESIZE_MODE_WIDTH;
            } else {
                $mode = self::RESIZE_MODE_HEIGHT;
            }
        }
        else
        {
            $mode = $this->getResizeMode();
        }

        if($mode === self::RESIZE_MODE_WIDTH)
        {
            if(!$scaleUpAllowed && $size->getWidth() > $this->getWidth()) {
                return $this;
            }

            return $this->resizeByWidth($size->getWidth());
        }

        if(!$scaleUpAllowed && $size->getHeight() > $this->getHeight()) {
            return $this;
        }

        return $this->resizeByHeight($size->getHeight());
    }

    // endregion

    // region: X - Array access interface

    /**
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->size[$offset]);
    }

    /**
     * @param string|int $offset
     * @return int|null
     */
    public function offsetGet($offset) : ?int
    {
        return $this->size[$offset] ?? null;
    }

    /**
     * @param string|int $offset
     * @param int $value
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        $this->size[$offset] = $value;
    }

    /**
     * @param string|int $offset
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        unset($this->size[$offset]);
    }

    // endregion
}
