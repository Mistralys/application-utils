<?php
/**
 * @package Application Utils
 * @subpackage ImageHelper
 * @see \AppUtils\ImageHelper\ImageTrimmer
 */

declare(strict_types=1);

namespace AppUtils\ImageHelper;

use AppUtils\ImageHelper;
use AppUtils\ImageHelper_Exception;
use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorFactory;
use GdImage;

/**
 * Specialized class used for trimming images.
 *
 * @package Application Utils
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ImageTrimmer
{
    /**
     * @var resource|GdImage
     */
    private $sourceImage;
    private RGBAColor $trimColor;
    private ImageHelper $helper;

    /**
     * @param ImageHelper $helper
     * @param resource|GdImage $sourceImage
     * @param RGBAColor|null $trimColor
     * @throws ImageHelper_Exception
     */
    public function __construct(ImageHelper $helper, $sourceImage, ?RGBAColor $trimColor=null)
    {
        ImageHelper::requireResource($sourceImage);

        $this->helper = $helper;
        $this->sourceImage = $sourceImage;
        $this->trimColor = $this->detectTrimColor($trimColor);
    }

    private function detectTrimColor(?RGBAColor $trimColor) : RGBAColor
    {
        $targetColor = ColorFactory::createAuto($trimColor);

        if($targetColor !== null)
        {
            return $targetColor;
        }

        return $this->helper->getIndexedColors(
            $this->sourceImage,
            (int)imagecolorat($this->sourceImage, 0, 0)
        );
    }

    /**
     * @return resource|GdImage|null
     */
    public function trim_new()
    {
        $img = imagecropauto(
            $this->sourceImage,
            IMG_CROP_THRESHOLD,
            0.1, // Does not work at all with 0.0
            imagecolorclosestalpha(
                $this->sourceImage,
                $this->trimColor->getRed()->get8Bit(),
                $this->trimColor->getGreen()->get8Bit(),
                $this->trimColor->getBlue()->get8Bit(),
                $this->trimColor->getAlpha()->get7Bit()
            )
        );

        if($img !== false) {
            return $img;
        }

        return null;
    }

    public function trim()
    {
        // Get the image width and height.
        $imw = imagesx($this->sourceImage);
        $imh = imagesy($this->sourceImage);

        // Set the X variables.
        $xmin = $imw;
        $xmax = 0;
        $ymin = null;
        $ymax = null;

        // Start scanning for the edges.
        for ($iy=0; $iy<$imh; $iy++)
        {
            $first = true;

            for ($ix=0; $ix<$imw; $ix++)
            {
                $ndx = imagecolorat($this->sourceImage, $ix, $iy);
                $colors = $this->helper->getIndexedColors($this->sourceImage, $ndx);

                if(!$colors->matchesAlpha($this->trimColor))
                {
                    if ($xmin > $ix) { $xmin = $ix; }
                    if ($xmax < $ix) { $xmax = $ix; }
                    if (!isset($ymin)) { $ymin = $iy; }

                    $ymax = $iy;

                    if($first)
                    {
                        $ix = $xmax;
                        $first = false;
                    }
                }
            }
        }

        // no trimming border found
        if($ymax === null) {
            return $this->sourceImage;
        }

        // The new width and height of the image.
        $imw = 1+$xmax-$xmin; // Image width in pixels
        $imh = 1+$ymax-$ymin; // Image height in pixels

        // Make another image to place the trimmed version in.
        $im2 = $this->helper->createNewImage($imw, $imh);

        if($this->trimColor->hasTransparency())
        {
            $bg2 = imagecolorallocatealpha(
                $im2,
                $this->trimColor->getRed()->get8Bit(),
                $this->trimColor->getGreen()->get8Bit(),
                $this->trimColor->getBlue()->get8Bit(),
                $this->trimColor->getAlpha()->get7Bit()
            );

            imagecolortransparent($im2, $bg2);
        }
        else
        {
            $bg2 = imagecolorallocate(
                $im2,
                $this->trimColor->getRed()->get8Bit(),
                $this->trimColor->getGreen()->get8Bit(),
                $this->trimColor->getBlue()->get8Bit()
            );
        }

        // Make the background of the new image the same as the background of the old one.
        imagefill($im2, 0, 0, $bg2);

        // Copy it over to the new image.
        imagecopy($im2, $this->sourceImage, 0, 0, $xmin, $ymin, $imw, $imh);

        return $im2;
    }
}
