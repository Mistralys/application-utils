<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\ImageHelper_Size;

/**
 * Creates an image size instance. Can be created the following ways:
 *
 * With width and height values
 *
 * <pre>
 * $size = imgSize(100, 80);
 * </pre>
 *
 * With a size array, indexed or associative with <code>width</code> and
 * <code>height</code> keys, or a mix of both.
 *
 * <pre>
 * $size = imgSize(array(100, 80));
 * $size = imgSize(array('width' => 100, 'height' => 80));
 * </pre>
 *
 * From an existing size instance: Creates a new instance with the same
 * width and height.
 *
 * <pre>
 * $size = imgSize(100, 80);
 * $copy = imgSize($size);
 * </pre>
 *
 * @param int|array<string|int,int>|ImageHelper_Size $widthArrayOrInstance
 * @param int|NULL $height
 * @return ImageHelper_Size
 */
function imgSize($widthArrayOrInstance, ?int $height=null) : ImageHelper_Size
{
    return ImageHelper_Size::create($widthArrayOrInstance, $height);
}