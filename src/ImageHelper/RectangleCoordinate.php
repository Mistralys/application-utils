<?php

declare(strict_types=1);

namespace AppUtils\ImageHelper;

use AppUtils\ImageHelper_Size;

class RectangleCoordinate
{
    private int $x;
    private int $y;
    private int $width;
    private int $height;

    public function __construct(int $x, int $y, int $width, int $height)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    public function getTopLeft() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->x,
            $this->y
        );
    }

    public function getTopRight() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->x + $this->width,
            $this->y
        );
    }

    public function getBottomLeft() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->x,
            $this->y + $this->height
        );
    }

    public function getBottomRight() : PixelCoordinate
    {
        return new PixelCoordinate(
            $this->x + $this->width,
            $this->y + $this->height
        );
    }

    public function getSize() : ImageHelper_Size
    {
        return new ImageHelper_Size(array(
            $this->x,
            $this->y
        ));
    }
}
