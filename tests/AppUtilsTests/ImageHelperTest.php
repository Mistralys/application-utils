<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;
use AppUtils\ImageHelper;
use function AppUtils\RGBAColor\imgSize;
use const TESTS_ROOT;

/**
 * @package Application Utils
 * @subpackage UnitTests
 */
final class ImageHelperTest extends TestCase
{
    // region: _Tests
    public const TEST_IMAGE_JPG = 'test-image.jpg';
    public const TEST_IMAGE_8_BIT_PNG = 'test-image-8-bit.png';
    public const TEST_IMAGE_8_BIT_TRANSPARENT_PNG = 'test-image-8-bit-transparent.png';
    public const TEST_IMAGE_24_BIT_PNG = 'test-image-24-bit.png';
    public const TEST_IMAGE_24_BIT_TRANSPARENT_PNG = 'test-image-24-bit-transparent.png';
    public const TEST_IMAGE_SVG = 'test-image.svg';
    public const TEST_IMAGE_NO_WIDTH_SVG = 'test-image-no-width.svg';

    /**
     * @var array<int,array<string,mixed>>
     */
    protected $testImages = array(
        array(
            'file' => self::TEST_IMAGE_JPG,
            'type' => 'jpeg',
            'size' => array(272, 158),
            'isVector' => false,
            'transparency' => false,
            'bits' => 8,
            'resample' => array(100, 58)
        ),
        array(
            'file' => self::TEST_IMAGE_8_BIT_PNG,
            'type' => 'png',
            'size' => array(272, 158),
            'isVector' => false,
            'transparency' => false,
            'bits' => 8,
            'resample' => array(100, 58)
        ),
        array(
            'file' => self::TEST_IMAGE_8_BIT_TRANSPARENT_PNG,
            'type' => 'png',
            'size' => array(272, 158),
            'isVector' => false,
            'transparency' => true,
            'bits' => 8
        ),
        array(
            'file' => self::TEST_IMAGE_24_BIT_PNG,
            'type' => 'png',
            'size' => array(272, 158),
            'isVector' => false,
            'transparency' => false,
            'bits' => 8
        ),
        array(
            'file' => self::TEST_IMAGE_24_BIT_TRANSPARENT_PNG,
            'type' => 'png',
            'size' => array(272, 158),
            'isVector' => false,
            'transparency' => true,
            'bits' => 8
        ),
        array(
            'file' => self::TEST_IMAGE_SVG,
            'type' => 'svg',
            'size' => array(210, 297),
            'isVector' => true,
            'transparency' => false,
            'bits' => 8
        ),
        array(
            'file' => self::TEST_IMAGE_NO_WIDTH_SVG,
            'type' => 'svg',
            'size' => array(210, 297),
            'isVector' => true,
            'transparency' => false,
            'bits' => 8
        )
    );

    /**
     * @see ImageHelper::getImageSize()
     */
    public function test_getImageSize() : void
    {
        foreach ($this->testImages as $entry)
        {
            $file = $entry['path'];
            $size = ImageHelper::getImageSize($file);
            $type = strtoupper($entry['type']);

            $this->assertEquals($entry['size'][0], $size[0], $type . ' width does not match in ' . $entry['file']);
            $this->assertEquals($entry['size'][1], $size[1], $type . ' height does not match in ' . $entry['file']);
            $this->assertEquals($entry['bits'], $size['bits'], $type . ' bit depth does not match in ' . $entry['file']);

            $helper = ImageHelper::createFromFile($file);

            $this->assertEquals($entry['isVector'], $helper->isVector());
            $this->assertEquals($entry['type'], ImageHelper::getFileImageType($file));

            $helper->dispose();
        }
    }

    public function test_getSizeByWidth() : void
    {
        foreach ($this->testImages as $entry)
        {
            if (!isset($entry['resample']))
            {
                continue;
            }

            $image = ImageHelper::createFromFile($entry['path']);
            $image->resampleByWidth($entry['resample'][0]);

            $size = $image->getSize();

            $this->assertEquals($entry['resample'][0], $size->getWidth());
            $this->assertEquals($entry['resample'][1], $size->getHeight());
        }
    }

    public function test_getAverageColor() : void
    {
        $img = ImageHelper::createFromFile($this->dataPath.'/test-image-fill-cc0000.png');

        $color = $img->calcAverageColor();

        $this->assertSame('CC0000', $color->toHEX());
    }

    public function test_getColorAt() : void
    {
        $path = $this->dataPath.'/test-image-dot-cc0000-center.png';
        $img = ImageHelper::createFromFile($path);

        $this->assertSame('FFFFFF', $img->getColorAt(0, 0)->toHEX(), '1x1');
        $this->assertSame('CC0000', $img->getColorAt(5, 5)->toHEX(), '5x5');
    }

    public function test_fillWhite() : void
    {
        $img = ImageHelper::createFromFile($this->dataPath.'/test-image-fill-cc0000.png');

        $this->assertNotSame('FFFFFF', $img->getColorAt(0, 0)->toHEX());

        $img->fillWhite();

        $this->assertSame('FFFFFF', $img->getColorAt(0, 0)->toHEX());
    }

    /**
     * When filling with a transparent color, only
     * the opacity value of the color is relevant.
     * The actual red/green/blue color values will
     * vary, because the helper assigns a random,
     * unused color to use as transparent color.
     */
    public function test_fillTransparent() : void
    {
        $img = ImageHelper::createFromFile($this->dataPath.'/test-image-fill-cc0000.png');

        $this->assertSame(0, $img->getColorAt(0, 0)->getAlpha()->get7Bit());

        $img->fillTransparent();

        $this->assertSame(127, $img->getColorAt(0, 0)->getAlpha()->get7Bit());
    }

    public function test_paste() : void
    {
        $path = $this->dataPath.'/test-image-dot-cc0000-center.png';
        $img1 = ImageHelper::createFromFile($path);

        // Fill the image with white
        $img1->fillWhite();

        $img2 = ImageHelper::createFromFile($path);

        // Paste the same image into the white area,
        // but offset by 1x1 pixel, changing the position
        // of the red dot. It is not at 5x5 anymore,
        // but at 6x6.
        $img1->paste($img2, 1, 1);

        $this->assertSame('CC0000', $img1->getColorAt(6, 6)->toHEX());
    }

    /**
     * Test the brightness with a white and a black image.
     */
    public function test_getBrightness() : void
    {
        $white = ImageHelper::createFromFile($this->dataPath.'/test-image-fill-ffffff.png');
        $this->assertSame(100.0, $white->getBrightness());

        $white = ImageHelper::createFromFile($this->dataPath.'/test-image-fill-000000.png');
        $this->assertSame(0.0, $white->getBrightness());
    }

    /**
     * To test the blurring, we use the image with the red
     * dot in its center. Before the blurring, an adjacent
     * pixel must be white. Afterwards, with the blurring, it
     * must not be entirely white anymore.
     */
    public function test_blur() : void
    {
        $img = ImageHelper::createFromFile($this->dataPath.'/test-image-dot-cc0000-center.png');

        $this->assertSame('FFFFFF', $img->getColorAt(4,4)->toHEX());

        $img->blur(50);

        $this->assertNotSame('FFFFFF', $img->getColorAt(4, 4)->toHEX());
    }

    /**
     * To test the cropping, we crop the image with the red
     * dot so only the red dot remains.
     */
    public function test_crop() : void
    {
        $img = ImageHelper::createFromFile($this->dataPath.'/test-image-dot-cc0000-center.png');

        $img->crop(1, 1, 5, 5);

        $this->assertSame('CC0000', $img->getColorAt(0, 0)->toHEX());
        $this->assertSame(1, $img->getWidth());
        $this->assertSame(1, $img->getHeight());
    }

    public function test_trim() : void
    {
        $img = ImageHelper::createFromFile($this->dataPath.'/test-image-black-square-white-trim.png');

        $img->trim(ColorFactory::createFromHEX('FFFFFF'));

        $this->assertSame(7, $img->getWidth());
        $this->assertSame(7, $img->getHeight());
        $this->assertSame('000000', $img->getColorAt(0, 0)->toHEX());
    }

    public function test_trimTransparent() : void
    {
        $img = ImageHelper::createFromFile($this->dataPath.'/test-image-black-square-transparent-trim.png');

        $this->assertSame(255, $img->getColorAt(0, 0)->getAlpha()->get8Bit());

        $img->trim();

        $this->assertSame(7, $img->getWidth());
        $this->assertSame(7, $img->getHeight());
        $this->assertSame('000000', $img->getColorAt(0, 0)->toHEX());
    }

    public function test_imgSizeFunction() : void
    {
        $size = imgSize(88, 33);
        $this->assertSame(88, $size->getWidth());
        $this->assertSame(33, $size->getHeight());

        $size = imgSize(array(88, 33));
        $this->assertSame(88, $size->getWidth());
        $this->assertSame(33, $size->getHeight());

        $size = imgSize(array('width' => 88, 'height' => 33));
        $this->assertSame(88, $size->getWidth());
        $this->assertSame(33, $size->getHeight());

        $size = imgSize(array(0 => 88, 'height' => 33));
        $this->assertSame(88, $size->getWidth());
        $this->assertSame(33, $size->getHeight());

        $size = imgSize(88, 33);
        $copy = imgSize($size);
        $this->assertSame(88, $copy->getWidth());
        $this->assertSame(33, $copy->getHeight());
        $this->assertNotSame($size, $copy);
    }

    public function test_resizeByWidth() : void
    {
        $size = imgSize(100, 40);
        $result = $size->resizeByWidth(60);

        $this->assertSame(60, $result->getWidth());
        $this->assertSame(24, $result->getHeight());
    }

    /**
     * BUG: Setting the height to 0 should not cause a PHP error.
     * It should be equivalent to setting it to NULL.
     */
    public function test_resizeByWidthZero() : void
    {
        $helper = $this->createTestImage(self::TEST_IMAGE_JPG);

        $helper->resample(300, 0);

        $this->addToAssertionCount(1);
    }

    public function test_resizeByHeight() : void
    {
        $size = imgSize(40, 100);
        $result = $size->resizeByHeight(60);

        $this->assertSame(24, $result->getWidth());
        $this->assertSame(60, $result->getHeight());
    }

    /**
     * Fitting a size into another, keeping the aspect
     * ratio, even if the orientations are not the same.
     */
    public function test_resizeInto() : void
    {
        $tests = array(
            array(
                'label' => 'Landscape to Square',
                'size' => array(100, 40),
                'target' => array(60, 60),
                'expected' => array(60, 24)
            ),
            array(
                'label' => 'Portrait to Square',
                'size' => array(40, 100),
                'target' => array(60, 60),
                'expected' => array(24, 60)
            ),
            array(
                'label' => 'Landscape to Landscape',
                'size' => array(100, 40),
                'target' => array(60, 40),
                'expected' => array(60, 24)
            ),
            array(
                'label' => 'Portrait to Landscape',
                'size' => array(40, 100),
                'target' => array(60, 40),
                'expected' => array(16, 40)
            ),
            array(
                'label' => 'Landscape to Portrait',
                'size' => array(100, 40),
                'target' => array(40, 60),
                'expected' => array(40, 16)
            ),
            array(
                'label' => 'Portrait to Portrait',
                'size' => array(40, 100),
                'target' => array(40, 60),
                'expected' => array(24, 60)
            )
        );

        foreach ($tests as $test)
        {
            $size = imgSize($test['size']);
            $targetSize = imgSize($test['target']);
            $newSize = $size->resizeInto($targetSize);
            $expected = imgSize($test['expected']);

            $label = $test['label'].PHP_EOL.
                'Size.......: '.$size->toReadableString().PHP_EOL.
                'Resize to..: '.$targetSize->toReadableString().PHP_EOL.
                'Result.....: '.$newSize->toReadableString().PHP_EOL.
                'Expected...: '.$expected->toReadableString();

            $this->assertSame($expected->getWidth(), $newSize->getWidth(), $label);
            $this->assertSame($expected->getHeight(), $newSize->getHeight(), $label);
        }
    }

    // endregion

    // region: Support methods

    protected ?string $dataPath = null;

    protected function setUp() : void
    {
        if (!function_exists('imagecreatefromjpeg'))
        {
            $this->markTestSkipped('GD functions are not available.');
        }

        if (isset($this->dataPath))
        {
            return;
        }

        $this->dataPath = TESTS_ROOT . '/assets/ImageHelper';

        foreach ($this->testImages as $idx => $entry)
        {
            $this->testImages[$idx]['path'] = $this->dataPath . '/' . $entry['file'];
        }
    }

    protected function createTestImage(string $imageName) : ImageHelper
    {
        return ImageHelper::createFromFile($this->getTestImagePath($imageName));
    }

    protected function getTestImagePath(string $imageName) : string
    {
        return $this->dataPath.'/'.$imageName;
    }

    // endregion
}
