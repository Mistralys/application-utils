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
    public function trim()
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
}
