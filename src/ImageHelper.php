<?php
/**
 * File containing the {@link ImageHelper} class.
 * 
 * @package Application Utils
 * @subpackage ImageHelper
 * @see ImageHelper
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ImageHelper\ComputedTextSize;
use AppUtils\ImageHelper\ImageTrimmer;
use AppUtils\RGBAColor\ColorException;
use AppUtils\RGBAColor\ColorFactory;
use GdImage;
use JsonException;

/**
 * Image helper class that can be used to transform images,
 * and retrieve information about them.
 * 
 * @package Application Utils
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ImageHelper
{
    public const ERROR_CANNOT_CREATE_IMAGE_CANVAS = 513001;
    public const ERROR_IMAGE_FILE_DOES_NOT_EXIST = 513002;
    public const ERROR_CANNOT_GET_IMAGE_SIZE = 513003;
    public const ERROR_UNSUPPORTED_IMAGE_TYPE = 513004;
    public const ERROR_FAILED_TO_CREATE_NEW_IMAGE = 513005;
    public const ERROR_SAVE_NO_IMAGE_CREATED = 513006;
    public const ERROR_CANNOT_WRITE_NEW_IMAGE_FILE = 513007;
    public const ERROR_CREATED_AN_EMPTY_FILE = 513008;
    public const ERROR_QUALITY_VALUE_BELOW_ZERO = 513009;
    public const ERROR_QUALITY_ABOVE_ONE_HUNDRED = 513010;
    public const ERROR_CANNOT_CREATE_IMAGE_OBJECT = 513011;
    public const ERROR_CANNOT_COPY_RESAMPLED_IMAGE_DATA = 513012;
    public const ERROR_HEADERS_ALREADY_SENT = 513013;
    public const ERROR_CANNOT_READ_SVG_IMAGE = 513014;
    public const ERROR_SVG_SOURCE_VIEWBOX_MISSING = 513015;
    public const ERROR_SVG_VIEWBOX_INVALID = 513016;
    public const ERROR_NOT_A_RESOURCE = 513017;
    public const ERROR_INVALID_STREAM_IMAGE_TYPE = 513018;
    public const ERROR_NO_TRUE_TYPE_FONT_SET = 513019;
    public const ERROR_POSITION_OUT_OF_BOUNDS = 513020;
    public const ERROR_IMAGE_CREATION_FAILED = 513021;
    public const ERROR_CANNOT_CREATE_IMAGE_CROP = 513023;
    public const ERROR_GD_LIBRARY_NOT_INSTALLED = 513024;
    public const ERROR_UNEXPECTED_COLOR_VALUE = 513025;
    public const ERROR_HASH_NO_IMAGE_LOADED = 513026;

    protected string $file = '';
    protected ImageHelper_Size $info;
    protected ?string $type = null;
    protected int $width;
    protected int $height;
    protected int $newWidth = 0;
    protected int $newHeight = 0;
    protected int $quality = 85;
    protected bool $alpha = false;
    protected string $TTFFile = '';

    /**
     * @var array<string,int>
     */
    protected array $colors = array();

    /**
    * @var resource|NULL
    */
    protected $newImage;

   /**
    * @var resource
    */
    protected $sourceImage;

    /**
     * @var array<string,string>
     */
    protected static array $imageTypes = array(
        'png' => 'png',
        'jpg' => 'jpeg',
        'jpeg' => 'jpeg',
        'gif' => 'gif',
        'svg' => 'svg'
    );

    /**
     * @var array<string,mixed>
     */
    protected static array $config = array(
        'auto-memory-adjustment' => true
    );

    /**
     * @var string[]
     */
    protected static array $streamTypes = array(
        'jpeg',
        'png',
        'gif'
    );

    /**
     * @param string|null $sourceFile
     * @param resource|GdImage|null $resource
     * @param string|null $type The image type, e.g. "png", "jpeg".
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws ImageHelper_Exception
     *
     * @see ImageHelper::ERROR_GD_LIBRARY_NOT_INSTALLED
     */
    public function __construct(?string $sourceFile=null, $resource=null, ?string $type=null)
    {
        // ensure that the GD library is installed
        if(!function_exists('imagecreate')) 
        {
            throw new ImageHelper_Exception(
                'The PHP GD extension is not installed or not enabled.',
                null,
                self::ERROR_GD_LIBRARY_NOT_INSTALLED
            );
        }
        
        if(is_resource($resource) || $resource instanceof GdImage)
        {
            $this->sourceImage = $resource;
            $this->type = $type;
            $this->info = self::getImageSize($resource);
        } 
        else 
        {
            $this->file = $sourceFile;
            if (!file_exists($this->file)) {
                throw new ImageHelper_Exception(
                    'Image file does not exist',
                    sprintf(
                        'Could not find the image file on disk at location [%s]',
                        $this->file
                    ),
                    self::ERROR_IMAGE_FILE_DOES_NOT_EXIST
                );
            }
    
            $type = self::getFileImageType($this->file);
            if ($type === null) {
                throw new ImageHelper_Exception(
                    'Error opening image',
                    'Not a valid supported image type for image ' . $this->file,
                    self::ERROR_UNSUPPORTED_IMAGE_TYPE
                );
            }

            $this->type = $type;
            $this->info = self::getImageSize($this->file);

            if(!$this->isVector()) 
            {
                $method = 'imagecreatefrom' . $this->type;
                $this->sourceImage = $method($this->file);
                if (!$this->sourceImage) {
                    throw new ImageHelper_Exception(
                        'Error creating new image',
                        $method . ' failed',
                        self::ERROR_FAILED_TO_CREATE_NEW_IMAGE
                    );
                }
                
                imagesavealpha($this->sourceImage, true);
            }
        }

        $this->width = $this->info->getWidth();
        $this->height = $this->info->getHeight();

        if(!$this->isVector()) {
            $this->setNewImage($this->duplicateImage($this->sourceImage));
        }
    }

   /**
    * Factory method: creates a new helper with a blank image.
    * 
    * @param integer $width
    * @param integer $height
    * @param string $type The target file type when saving
    * @return ImageHelper
    * @throws ImageHelper_Exception
    *
    * @see ImageHelper::ERROR_CANNOT_CREATE_IMAGE_OBJECT
    */
    public static function createNew(int $width, int $height, string $type='png') : self
    {
        $img = imagecreatetruecolor($width, $height);
        if($img !== false) {
            return self::createFromResource($img, $type);
        }
        
        throw new ImageHelper_Exception(
            'Could not create new true color image.',
            null,
            self::ERROR_CANNOT_CREATE_IMAGE_OBJECT
        );
    }

    /**
     * Factory method: creates an image helper from an
     * existing image resource.
     *
     * Note: while the resource is type independent, the
     * type parameter is required for some methods, as well
     * as to be able to save the image.
     *
     * @param resource $resource
     * @param string|null $type The target image type, e.g. "jpeg", "png", etc.
     * @return ImageHelper
     * @throws ImageHelper_Exception
     */
    public static function createFromResource($resource, ?string $type=null) : ImageHelper
    {
        self::requireResource($resource);
        
        return new ImageHelper(null, $resource, $type);
    }

    /**
     * Factory method: creates an image helper from an
     * image file on disk.
     *
     * @param string $file
     * @return ImageHelper
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws ImageHelper_Exception
     */
    public static function createFromFile(string $file) : ImageHelper
    {
        return new ImageHelper($file, null, self::getFileImageType($file));
    }
    
   /**
    * Sets a global image helper configuration value. Available
    * configuration settings are:
    * 
    * <ul>
    * <li><code>auto-memory-adjustment</code> <i>boolean</i> Whether to try and adjust the memory limit automatically so there will be enough to load/process the target image.</li>
    * </ul>
    * 
    * @param string $name
    * @param mixed|NULL $value
    */
    public static function setConfig(string $name, $value) : void
    {
        if(isset(self::$config[$name])) {
            self::$config[$name] = $value;
        }
    }

   /**
    * Shorthand for setting the automatic memory adjustment
    * global configuration setting.
    *
    * @param bool $enabled
    * @return void
    */
    public static function setAutoMemoryAdjustment(bool $enabled=true) : void
    {
        self::setConfig('auto-memory-adjustment', $enabled);
    }

    /**
     * Duplicates an image resource.
     * @param resource $img
     * @return resource
     * @throws ImageHelper_Exception
     */
    protected function duplicateImage($img)
    {
        self::requireResource($img);
        
        $width = imagesx($img);
        $height = imagesy($img);
        $duplicate = $this->createNewImage($width, $height);
        imagecopy($duplicate, $img, 0, 0, 0, 0, $width, $height);
        return $duplicate;
    }

    /**
     * Duplicates the current state of the image into a new
     * image helper instance.
     *
     * @return ImageHelper
     * @throws ImageHelper_Exception
     */
    public function duplicate() : ImageHelper
    {
        return self::createFromResource($this->duplicateImage($this->newImage));
    }

    /**
     * @return $this
     * @throws ImageHelper_Exception
     */
    public function enableAlpha() : self
    {
        if(!$this->alpha) 
        {
            self::addAlphaSupport($this->newImage, false);
            $this->alpha = true;
        }
        
        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     * @return $this
     * @throws ImageHelper_Exception
     */
    public function resize(int $width, int $height) : self
    {
        $new = $this->createNewImage($width, $height);
        
        imagecopy($new, $this->newImage, 0, 0, 0, 0, $width, $height);
        
        $this->setNewImage($new);
        
        return $this;
    }

    /**
     * @return array{0:int,1:int}
     */
    public function getNewSize() : array
    {
        return array($this->newWidth, $this->newHeight);
    }
    
    /**
     * Sharpens the image by the specified percentage.
     *
     * @param int|float $percent
     * @return $this
     */
    public function sharpen($percent=0) : self
    {
        if($percent <= 0) {
            return $this;
        }
        
        // the factor goes from 0 to 64 for sharpening.
        $factor = $percent * 64 / 100;
        return $this->convolute($factor);
    }

    /**
     * @param int|float $percent
     * @return $this
     */
    public function blur($percent=0) : self
    {
        if($percent <= 0) {
            return $this;
        }
        
        // the factor goes from -64 to 0 for blurring.
        $factor = ($percent * 64 / 100) * -1;
        return $this->convolute($factor);
    }

    /**
     * @param int|float $factor
     * @return $this
     */
    protected function convolute($factor) : self
    {
        // get a value that's equal to 64 - abs( factor )
        // ( using min/max to limit the factor to 0 - 64 to not get out of range values )
        $val1Adjustment = 64 - min( 64, max( 0, abs( $factor ) ) );
        
        // the base factor for the "current" pixel depends on if we are blurring or sharpening.
        // If we are blurring use 1, if sharpening use 9.
        $val1Base = 9;
        if( abs( $factor ) !== $factor ) {
            $val1Base = 1;
        }
        
        // value for the center/current pixel is:
        //  1 + 0 - max blurring
        //  1 + 64- minimal blurring
        //  9 + 64- minimal sharpening
        //  9 + 0 - maximum sharpening
        $val1 = $val1Base + $val1Adjustment;
        
        // the value for the surrounding pixels is either positive or negative depending on if we are blurring or sharpening.
        $val2 = -1;
        if( abs( $factor ) !== $factor ) {
            $val2 = 1;
        }
        
        // setup matrix ..
        $matrix = array(
            array( $val2, $val2, $val2 ),
            array( $val2, $val1, $val2 ),
            array( $val2, $val2, $val2 )
        );
        
        // calculate the correct divisor
        // actual divisor is equal to "$divisor = $val1 + $val2 * 8;"
        // but the following line is more generic
        $divisor = array_sum( array_map( 'array_sum', $matrix ) );
        
        // apply the matrix
        imageconvolution( $this->newImage, $matrix, $divisor, 0 );
        
        return $this;
    }
    
    /**
     * Whether the image is an SVG image.
     * @return boolean
     */
    public function isTypeSVG() : bool
    {
        return $this->type === 'svg';
    }
    
    /**
     * Whether the image is a PNG image.
     * @return boolean
     */
    public function isTypePNG() : bool
    {
        return $this->type === 'png';
    }
    
    /**
     * Whether the image is a JPEG image.
     * @return boolean
     */
    public function isTypeJPEG() : bool
    {
        return $this->type === 'jpeg';
    }
    
    /**
     * Whether the image is a vector image.
     * @return boolean
     */
    public function isVector() : bool
    {
        return $this->isTypeSVG();
    }
    
    /**
     * Retrieves the type of the image.
     * @return string e.g. "jpeg", "png"
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    /**
     * Calculates the size of the image by the specified width,
     * and returns an indexed array with the width and height size.
     *
     * @param integer $width
     * @return ImageHelper_Size
     */
    public function getSizeByWidth(int $width) : ImageHelper_Size
    {
        $height = (int)floor(($width * $this->height) / $this->width);
        
        return new ImageHelper_Size(array(
            $width,
            $height,
            $this->info['bits'],
            $this->info['channels']
        ));
    }
    
    /**
     * Calculates the size of the image by the specified height,
     * and returns an indexed array with the width and height size.
     *
     * @param integer $height
     * @return ImageHelper_Size
     */
    public function getSizeByHeight(int $height) : ImageHelper_Size
    {
        $width = (int)floor(($height * $this->width) / $this->height);
        
        return new ImageHelper_Size(array(
            $width,
            $height,
            $this->info['bits'],
            $this->info['channels']
        ));
    }

    /**
     * Resamples the image to a new width, maintaining
     * aspect ratio.
     *
     * @param int $width
     * @return ImageHelper
     * @throws ImageHelper_Exception
     */
    public function resampleByWidth(int $width) : ImageHelper
    {
        $size = $this->getSizeByWidth($width);

        $this->resampleImage($size->getWidth(), $size->getHeight());
        
        return $this;
    }

    /**
     * Resamples the image by height, and creates a new image file on disk.
     *
     * @param int $height
     * @return ImageHelper
     * @throws ImageHelper_Exception
     */
    public function resampleByHeight(int $height) : ImageHelper
    {
        $size = $this->getSizeByHeight($height);

        return $this->resampleImage($size->getWidth(), $size->getHeight());
    }

    /**
     * Resamples the image without keeping the aspect ratio.
     *
     * @param int|null $width
     * @param int|null $height
     * @return ImageHelper
     * @throws ImageHelper_Exception
     */
    public function resample(?int $width = null, ?int $height = null) : ImageHelper
    {
        if($this->isVector()) {
            return $this;
        }
        
        if ($width === null && $height === null) {
            return $this->resampleByWidth($this->width);
        }

        if ($width === null) {
            return $this->resampleByHeight($height);
        }

        if ($height === null) {
            return $this->resampleByWidth($width);
        }

        return $this->resampleAndCrop($width, $height);
    }

    public function resampleAndCrop(int $width, int $height) : ImageHelper
    {
        if($this->isVector()) {
            return $this;
        }

        if ($this->width <= $this->height) 
        {
            $this->resampleByWidth($width);
        } 
        else 
        {
            $this->resampleByHeight($height);
        }
        
        $newCanvas = $this->createNewImage($width, $height);
        
        // and now we can add the crop
        if (!imagecopy(
            $newCanvas,
            $this->newImage,
            0, // destination X
            0, // destination Y
            0, // source X
            0, // source Y
            $width,
            $height
        )
        ) {
            throw new ImageHelper_Exception(
                'Error creating new image',
                'Cannot create crop of the image',
                self::ERROR_CANNOT_CREATE_IMAGE_CROP
            );
        }

        $this->setNewImage($newCanvas);

        return $this;
    }
    
    /**
     * Configures the specified image resource to make it alpha compatible.
     *
     * @param resource $canvas
     * @param bool $fill Whether to fill the whole canvas with the transparency
     * @throws ImageHelper_Exception
     */
    public static function addAlphaSupport($canvas, bool $fill=true) : void
    {
        self::requireResource($canvas);
        
        imagealphablending($canvas,true);
        imagesavealpha($canvas, true);

        if($fill) {
            self::fillImageTransparent($canvas);
        }
    }
    
    public function isAlpha() : bool
    {
        return $this->alpha;
    }

    /**
     * @param string $targetFile
     * @param bool $dispose
     * @return $this
     * @throws ImageHelper_Exception
     */
    public function save(string $targetFile, bool $dispose=true) : self
    {
        if($this->isVector()) {
            return $this;
        }
        
        if(!is_resource($this->newImage)) {
            throw new ImageHelper_Exception(
                'Error creating new image',
                'Cannot save an image, no valid image resource was created. You have to call one of the resample methods to create a new image.',
                self::ERROR_SAVE_NO_IMAGE_CREATED
            );
        }

        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        
        $method = 'image' . $this->type;
        if (!$method($this->newImage, $targetFile, $this->resolveQuality())) {
            throw new ImageHelper_Exception(
                'Error creating new image',
                sprintf(
                    'The %s method could not write the new image to %s',
                    $method,
                    $targetFile
                ),
                self::ERROR_CANNOT_WRITE_NEW_IMAGE_FILE
            );
        }

        if (filesize($targetFile) < 1) {
            throw new ImageHelper_Exception(
                'Error creating new image',
                'Resampling completed successfully, but the generated file is 0 bytes big.',
                self::ERROR_CREATED_AN_EMPTY_FILE
            );
        }

        if($dispose) {
            $this->dispose();
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    public function dispose() : self
    {
        if(is_resource($this->sourceImage)) {
            imagedestroy($this->sourceImage);
        }
        
        if(is_resource($this->newImage)) {
            imagedestroy($this->newImage);
        }

        return $this;
    }

    protected function resolveQuality() : int
    {
        switch ($this->type)
        {
            case 'jpeg':
                return $this->quality;

            case 'png':
            default:
                return 0;
        }
    }

    /**
     * Sets the quality for image types like jpg that use compression.
     *
     * @param int $quality
     * @return ImageHelper
     * @throws ImageHelper_Exception
     */
    public function setQuality(int $quality) : self
    {
        if ($quality < 0) {
            throw new ImageHelper_Exception(
                'Invalid configuration',
                'Cannot set a quality less than 0.',
                self::ERROR_QUALITY_VALUE_BELOW_ZERO
            );
        }

        if ($quality > 100) {
            throw new ImageHelper_Exception(
                'Invalid configuration',
                'Cannot set a quality higher than 100.',
                self::ERROR_QUALITY_ABOVE_ONE_HUNDRED
            );
        }

        $this->quality = $quality;

        return $this;
    }

   /**
    * Attempts to adjust the memory to the required size
    * to work with the current image.
    * 
    * @return boolean
    */
    protected function adjustMemory() : bool
    {
        if(!self::$config['auto-memory-adjustment']) {
            return true;
        }
        
        $MB = 1048576; // number of bytes in 1M
        $K64 = 65536; // number of bytes in 64K
        $tweakFactor = 25; // magic adjustment value as safety threshold
        $memoryNeeded = ceil(
            (
                $this->info->getWidth() 
                * 
                $this->info->getHeight() 
                * 
                $this->info->getBits() 
                * 
                ($this->info->getChannels() / 8) 
                + 
                $K64
            )
            * $tweakFactor
        );

        // ini_get('memory_limit') only works if compiled with "--enable-memory-limit".
        // Also, default memory limit is 8MB, so we will stick with that.
        $memoryLimit = 8 * $MB;
            
        if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > $memoryLimit) {
            $newLimit = ($memoryLimit + (memory_get_usage() + $memoryNeeded)) / $MB;
            $newLimit = ceil($newLimit);
            ini_set('memory_limit', $newLimit . 'M');

            return true;
        }

        return false;
    }

    /**
     * Stretches the image to the specified dimensions.
     *
     * @param int $width
     * @param int $height
     * @return ImageHelper
     * @throws ImageHelper_Exception
     */
    public function stretch(int $width, int $height) : ImageHelper
    {
        return $this->resampleImage($width, $height);
    }

   /**
    * Creates a new image from the current image,
    * resampling it to the new size.
    * 
    * @param int $newWidth
    * @param int $newHeight   
    * @throws ImageHelper_Exception
    * @return ImageHelper
    */
    protected function resampleImage(int $newWidth, int $newHeight) : ImageHelper
    {
        if($this->isVector()) {
            return $this;
        }

        if($this->newWidth===$newWidth && $this->newHeight===$newHeight) {
            return $this;
        }
        
        if($newWidth < 1) { $newWidth = 1; }
        if($newHeight < 1) { $newHeight = 1; }
        
        $this->adjustMemory();

        $new = $this->createNewImage($newWidth, $newHeight);
       
        if (!imagecopyresampled($new, $this->newImage, 0, 0, 0, 0, $newWidth, $newHeight, $this->newWidth, $this->newHeight)) 
        {
            throw new ImageHelper_Exception(
                'Error creating new image',
                'Cannot copy resampled image data',
                self::ERROR_CANNOT_COPY_RESAMPLED_IMAGE_DATA
            );
        }

        $this->setNewImage($new);

        return $this;
    }

    /**
     * Gets the image type for the specified file name.
     * Like {@link getImageType()}, except that it automatically
     * extracts the file extension from the file name.
     *
     * @param string |resource|GdImage $pathOrResource
     * @return string|NULL
     * @see getImageType()
     */
    public static function getFileImageType($pathOrResource) : ?string
    {
        if(!is_string($pathOrResource)) {
            return 'png';
        }

        return self::getImageType(strtolower(pathinfo($pathOrResource, PATHINFO_EXTENSION)));
    }

    /**
     * Gets the image type for the specified file extension,
     * or NULL if the extension is not among the supported
     * file types.
     *
     * @param string $extension
     * @return string|NULL
     */
    public static function getImageType(string $extension) : ?string
    {
        return self::$imageTypes[$extension] ?? null;
    }

    /**
     * @return string[]
     */
    public static function getImageTypes() : array
    {
        $types = array_values(self::$imageTypes);
        return array_unique($types);
    }
    
   /**
    * Displays an existing image resource.
    *
    * @param resource $resource
    * @param string $imageType The image format to send, i.e. "jpeg", "png"
    * @param int $quality The quality to use for the image. This is 0-9 (0=no compression, 9=max) for PNG, and 0-100 (0=lowest, 100=highest quality) for JPG
    *
    * @throws ImageHelper_Exception
    * @see ImageHelper::ERROR_NOT_A_RESOURCE
    * @see ImageHelper::ERROR_INVALID_STREAM_IMAGE_TYPE
    */
    public static function displayImageStream($resource, string $imageType, int $quality=-1) : void
    {
        self::requireResource($resource);

        $imageType = self::requireValidStreamType($imageType);
        
        header('Content-type:image/' . $imageType);

        $function = 'image' . $imageType;
        
        $function($resource, null, $quality);
    }

    /**
     * @param string $imageType
     * @return string
     *
     * @throws ImageHelper_Exception
     * @see ImageHelper::ERROR_INVALID_STREAM_IMAGE_TYPE
     * @see ImageHelper::$streamTypes
     */
    public static function requireValidStreamType(string $imageType) : string
    {
        $imageType = strtolower($imageType);

        if(in_array($imageType, self::$streamTypes))
        {
            return $imageType;
        }

        throw new ImageHelper_Exception(
            'Invalid image stream type',
            sprintf(
                'The image type [%s] cannot be used for a stream.',
                $imageType
            ),
            self::ERROR_INVALID_STREAM_IMAGE_TYPE
        );
    }

    /**
     * Displays an image from an existing image file.
     * @param string $imageFile
     * @throws ImageHelper_Exception
     */
    public static function displayImage(string $imageFile) : void
    {
        $file = null;
        $line = null;
        if (headers_sent($file, $line)) {
            throw new ImageHelper_Exception(
                'Error displaying image',
                'Headers have already been sent: in file ' . $file . ':' . $line,
                self::ERROR_HEADERS_ALREADY_SENT
            );
        }

        if (!file_exists($imageFile)) {
            throw new ImageHelper_Exception(
                'Image file does not exist',
                sprintf(
                    'Cannot display image, the file does not exist on disk: [%s].',
                    $imageFile
                ),
                self::ERROR_IMAGE_FILE_DOES_NOT_EXIST
            );
        }

        $format = self::getFileImageType($imageFile);
        if($format === 'svg') {
            $format = 'svg+xml';
        }

        $contentType = 'image/' . $format;
        
        header('Content-Type: '.$contentType);
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($imageFile)) . " GMT");
        header('Cache-Control: public');
        header('Content-Length: ' . filesize($imageFile));

        readfile($imageFile);
    }
    
   /**
    * Displays the current image.
    *
    * NOTE: You must call `exit()` manually after this.
    */
    public function display() : void
    {
        self::displayImageStream(
            $this->newImage,
            $this->getType(),
            $this->resolveQuality()
        );
    }

    /**
     * Trims the current loaded image.
     *
     * @param RGBAColor|null $color A color definition, as an associative array with red, green, and blue keys. If not specified, the color at pixel position 0,0 will be used.
     *
     * @return ImageHelper
     * @throws ImageHelper_Exception
     * @throws ColorException
     *
     * @see ImageHelper::ERROR_NOT_A_RESOURCE
     * @see ImageHelper::ERROR_CANNOT_CREATE_IMAGE_CANVAS
     */
    public function trim(?RGBAColor $color=null) : ImageHelper
    {
        return $this->trimImage($this->newImage, $color);
    }

    /**
     * Retrieves a color definition by its index.
     *
     * @param resource $img A valid image resource.
     * @param int $colorIndex The color index, as returned by `imagecolorat` for example.
     * @return RGBAColor An array with red, green, blue and alpha keys.
     *
     * @throws ImageHelper_Exception
     * @see ImageHelper::ERROR_NOT_A_RESOURCE
     */
    public function getIndexedColors($img, int $colorIndex) : RGBAColor
    {
        self::requireResource($img);

        return ColorFactory::createFromIndex($img, $colorIndex);
    }

    /**
     * @param resource $img
     * @param int $x
     * @param int $y
     * @return RGBAColor
     * @throws ImageHelper_Exception
     */
    public function getIndexedColorsAt($img, int $x, int $y) : RGBAColor
    {
        self::requireResource($img);

        return $this->getIndexedColors(
            $img,
            imagecolorat($this->sourceImage, $x, $y)
        );
    }

    /**
     * Trims the specified image resource by removing the specified color.
     * Also works with transparency.
     *
     * @param resource|GdImage $img
     * @param RGBAColor|null $trimColor
     * @return ImageHelper
     *
     * @throws ImageHelper_Exception
     */
    protected function trimImage($img, ?RGBAColor $trimColor=null) : ImageHelper
    {
        if($this->isVector()) {
            return $this;
        }

        self::requireResource($img);

        $trimmer = new ImageTrimmer($this, $img, $trimColor);
        $new = $trimmer->trim();

        if($new === null) {
            return $this;
        }
        
        // To finish up, we replace the old image which is referenced.
        imagedestroy($img);
        
        $this->setNewImage($new);

        return $this;
    }

    /**
     * Sets the new image after a transformation operation:
     * automatically adjusts the new size information.
     *
     * @param resource $image
     *
     * @throws ImageHelper_Exception
     * @see ImageHelper::ERROR_NOT_A_RESOURCE
     */
    protected function setNewImage($image) : ImageHelper
    {
        self::requireResource($image);
        
        $this->newImage = $image;
        $this->newWidth = imagesx($image);
        $this->newHeight= imagesy($image);

        return $this;
    }
    
   /**
    * Requires the subject to be a resource.
    * 
    * @param resource|GdImage|mixed $subject
    *
    * @throws ImageHelper_Exception
    * @see ImageHelper::ERROR_NOT_A_RESOURCE
    */
    public static function requireResource($subject) : void
    {
        if(is_resource($subject) && imagesx($subject)) {
            return;
        }

        if($subject instanceof GdImage) {
            return;
        }
        
        throw new ImageHelper_Exception(
            'Not an image resource',
            sprintf(
                'Specified image should be a resource, [%s] given.',
                gettype($subject)
            ),
            self::ERROR_NOT_A_RESOURCE
        );
    }
    
   /**
    * Creates a new image resource, with transparent background.
    * 
    * @param int $width
    * @param int $height
    * @throws ImageHelper_Exception
    * @return resource
    */
    public function createNewImage(int $width, int $height)
    {
        $img = imagecreatetruecolor($width, $height);
        
        if($img === false) 
        {
            throw new ImageHelper_Exception(
                'Error creating new image',
                'Cannot create new image canvas',
                self::ERROR_CANNOT_CREATE_IMAGE_CANVAS
            );
        }

        self::addAlphaSupport($img);
        
        return $img;
    }

    /**
     * @param int $x
     * @param int $y
     * @return $this
     */
	public function fillWhite(int $x=0, int $y=0) : self
	{
	    $this->addRGBColor('white', 255, 255, 255);
        return $this->fill('white', $x, $y);
	}

    /**
     * @return $this
     * @throws ImageHelper_Exception
     */
	public function fillTransparent() : self
	{
        $this->enableAlpha();
	    
	    self::fillImageTransparent($this->newImage);
	    
	    return $this;
	}

    /**
     * @param resource $resource
     * @return void
     * @throws ImageHelper_Exception
     */
	public static function fillImageTransparent($resource) : void
	{
	    self::requireResource($resource);
	    
	    $transparent = imagecolorallocatealpha($resource, 89, 14, 207, 127);
	    imagecolortransparent ($resource, $transparent);
	    imagefill($resource, 0, 0, $transparent);
	}

    /**
     * @param string $colorName
     * @param int $x
     * @param int $y
     * @return $this
     */
	public function fill(string $colorName, int $x=0, int $y=0) : self
	{
	    imagefill($this->newImage, $x, $y, $this->colors[$colorName]);
	    return $this;
	}

    /**
     * @param string $name
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return $this
     */
    public function addRGBColor(string $name, int $red, int $green, int $blue) : self
    {
        $this->colors[$name] = (int)imagecolorallocate($this->newImage, $red, $green, $blue);
        return $this;
    }

    /**
     * @param string $text
     * @param int|float $size
     * @param string $colorName
     * @param int $x
     * @param int $y
     * @param int|float $angle
     * @return $this
     */
    public function textTTF(string $text, $size, string $colorName, int $x=0, int $y=0, $angle=0) : self
    {
        imagealphablending($this->newImage, true);
        
        imagettftext(
            $this->newImage,
            (float)$size,
            (float)$angle,
            $x,
            $y,
            $this->colors[$colorName],
            $this->TTFFile,
            $text
        );
        
        imagealphablending($this->newImage, false);
        
        return $this;
    }
    
   /**
    * @return resource
    */
    public function getImage()
    {
        return $this->newImage;
    }

    /**
     * @param ImageHelper $target
     * @param int $xpos
     * @param int $ypos
     * @param int $sourceX
     * @param int $sourceY
     * @return $this
     * @throws ImageHelper_Exception
     */
    public function paste(ImageHelper $target, int $xpos=0, int $ypos=0, int $sourceX=0, int $sourceY=0) : self
    {
        $img = $target->getImage();
        
        if($target->isAlpha()) {
            $this->enableAlpha();
        }
        
        imagecopy(
            $this->newImage,
            $img,
            $xpos,
            $ypos,
            $sourceX,
            $sourceY,
            imagesx($img),
            imagesy($img)
        );

        return $this;
    }

    /**
     * Retrieves the size of the image.
     *
     * @return ImageHelper_Size
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws ImageHelper_Exception
     *
     * @see ImageHelper::ERROR_CANNOT_GET_IMAGE_SIZE
     */
	public function getSize() : ImageHelper_Size
    {
	    return self::getImageSize($this->newImage);
    }
    
   /**
    * Sets the TTF font file to use for text operations.
    * 
    * @param string $filePath
    * @return $this
    */
    public function setFontTTF(string $filePath) : self
    {
        $this->TTFFile = $filePath;
        return $this;
    }

    /**
     * Goes through a series of text sizes to find the closest match to
     * fit the text into the target width.
     *
     * @param string $text
     * @param integer $matchWidth
     * @return ComputedTextSize
     * @throws ImageHelper_Exception
     */
    public function fitText(string $text, int $matchWidth) : ComputedTextSize
    {
        /**
         * @var ComputedTextSize[]
         */
        $sizes = array();

        for($i=1; $i<=1000; $i += 0.1) {
            $size = $this->calcTextSize($text, $i);
            $sizes[] = $size;
            if($size->getWidth() >= $matchWidth) {
                break;
            }
        }
    
        $last = array_pop($sizes);
        $prev = array_pop($sizes);
    
        // determine which is the closest match, and use that
        $diffLast = $last->getWidth() - $matchWidth;
        $diffPrev = $matchWidth - $prev->getWidth();
    
        if($diffLast <= $diffPrev) {
            return $last;
        }
    
        return $prev;
    }

    /**
     * @param string $text
     * @param int|float $fontSize
     * @return ComputedTextSize
     * @throws ImageHelper_Exception
     */
    public function calcTextSize(string $text, $fontSize) : ComputedTextSize
    {
        $this->requireTTFFont();

        $box = imagettfbbox((float)$fontSize, 0, $this->TTFFile, $text);
    
        return new ComputedTextSize(array(
            'size' => (float)$fontSize,
            'top_left_x' => $box[6],
            'top_left_y' => $box[7],
            'top_right_x' => $box[4],
            'top_right_y' => $box[5],
            'bottom_left_x' => $box[0],
            'bottom_left_y' => $box[1],
            'bottom_right_x' => $box[2],
            'bottom_right_y' => $box[3],
            'width' => $box[4]-$box[0],
            'height' => $box[1]-$box[7]
        ));
    }
    
    protected function requireTTFFont() : void
    {
        if(!empty($this->TTFFile)) {
            return;
        }
        
	    throw new ImageHelper_Exception(
            'No true type font specified',
            'This functionality requires a TTF font file to be specified with the [setFontTTF] method.',
            self::ERROR_NO_TRUE_TYPE_FONT_SET    
        );
    }

    /**
     * Retrieves the size of an image file on disk, or
     * an existing image resource.
     *
     * @param resource|GdImage|string $pathOrResource
     * @return ImageHelper_Size Size object, can also be accessed like the traditional array from getimagesize
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws ImageHelper_Exception
     *
     * @see ImageHelper_Size
     * @see ImageHelper::ERROR_CANNOT_GET_IMAGE_SIZE
     * @see ImageHelper::ERROR_CANNOT_READ_SVG_IMAGE
     * @see ImageHelper::ERROR_SVG_SOURCE_VIEWBOX_MISSING
     * @see ImageHelper::ERROR_SVG_VIEWBOX_INVALID
     */
	public static function getImageSize($pathOrResource) : ImageHelper_Size
	{
	    if(is_resource($pathOrResource) || $pathOrResource instanceof GdImage)
	    {
	        return new ImageHelper_Size(array(
	            'width' => imagesx($pathOrResource),
	            'height' => imagesy($pathOrResource),
	            'channels' => 1,
	            'bits' => 8
	        ));
	    }

	    $type = self::getFileImageType($pathOrResource);

        $sizeMethods = array(
            'svg' => array(self::class, 'getImageSize_svg')
        );

	    if(isset($sizeMethods[$type]))
	    {
	        return ClassHelper::requireObjectInstanceOf(
                ImageHelper_Size::class,
                $sizeMethods[$type]($pathOrResource)
            );
	    }

	    $info = getimagesize($pathOrResource);

	    if($info !== false) {
	        return new ImageHelper_Size($info);
	    }
	    
        throw new ImageHelper_Exception(
            'Error opening image file',
            sprintf(
                'Could not get image size for image [%s]',
                $pathOrResource
            ),
            self::ERROR_CANNOT_GET_IMAGE_SIZE
        );
	}

    /**
     * @param string $imagePath
     * @return ImageHelper_Size
     *
     * @throws ImageHelper_Exception
     * @throws XMLHelper_Exception
     * @throws JsonException
     */
	protected static function getImageSize_svg(string $imagePath) : ImageHelper_Size
	{
	    $xml = XMLHelper::createSimplexml();
	    $xml->loadFile($imagePath);
	    
	    if($xml->hasErrors()) {
	        throw new ImageHelper_Exception(
	            'Error opening SVG image',
	            sprintf(
	                'The XML content of the image [%s] could not be parsed.',
	                $imagePath
                ),
	            self::ERROR_CANNOT_READ_SVG_IMAGE
            );
	    }
	    
	    $data = $xml->toArray();
	    $xml->dispose();
	    unset($xml);
	    
	    if(!isset($data['@attributes']['viewBox'])) {
	        throw new ImageHelper_Exception(
	            'SVG Image is corrupted',
	            sprintf(
	                'The [viewBox] attribute is missing in the XML of the image at path [%s].',
	                $imagePath
                ),
	            self::ERROR_SVG_SOURCE_VIEWBOX_MISSING
            );
	    }
	    
	    $svgWidth = parseNumber($data['@attributes']['width'])->getNumber();
	    $svgHeight = parseNumber($data['@attributes']['height'])->getNumber();
	    
	    $viewBox = str_replace(' ', ',', $data['@attributes']['viewBox']);
	    $size = explode(',', $viewBox);
	    
	    if(count($size) !== 4)
	    {
	        throw new ImageHelper_Exception(
	            'SVG image has an invalid viewBox attribute',
	            sprintf(
	               'The [viewBox] attribute does not have an expected value: [%s] in path [%s].',
	                $viewBox,
	                $imagePath
                ),
	            self::ERROR_SVG_VIEWBOX_INVALID
            );
	    }
	    
	    $boxWidth = (float)$size[2];
	    $boxHeight = (float)$size[3];
	    
	    // calculate the x and y units of the document: 
	    // @see http://tutorials.jenkov.com/svg/svg-viewport-view-box.html#viewbox
	    //
	    // The viewbox combined with the width and height of the svg
	    // allow calculating how many pixels are in one unit of the 
	    // width and height of the document.
        //
	    $xUnits = $svgWidth / $boxWidth;
	    $yUnits = $svgHeight / $boxHeight;
	    
	    $pxWidth = $xUnits * $svgWidth;
	    $pxHeight = $yUnits * $svgHeight;
	    
	    return new ImageHelper_Size(array(
            (int)$pxWidth,
            (int)$pxHeight,
	        'bits' => 8
	    ));
	}

    /**
     * Crops the image to the specified width and height, optionally
     * specifying the origin position to crop from.
     *
     * @param integer $width
     * @param integer $height
     * @param integer $x
     * @param integer $y
     * @return $this
     * @throws ImageHelper_Exception
     */
    public function crop(int $width, int $height, int $x=0, int $y=0) : ImageHelper
    {
        $new = $this->createNewImage($width, $height);
        
        imagecopy($new, $this->newImage, 0, 0, $x, $y, $width, $height);
        
        $this->setNewImage($new);
        
        return $this;
    }
    
    public function getWidth() : int
    {
        return $this->newWidth;
    }
    
    public function getHeight() : int
    {
        return $this->newHeight;
    }

    /**
     * Calculates the average color value used in
     * the image. Returns an associative array
     * with the red, green, blue and alpha components,
     * or a HEX color string depending on the selected
     * format.
     *
     * NOTE: Use the calcAverageColorXXX methods for
     * strict return types.
     *
     * @return RGBAColor
     *
     * @throws ImageHelper_Exception
     *
     * @see ImageHelper::calcAverageColorHEX()
     * @see ImageHelper::calcAverageColorRGB()
     */
    public function calcAverageColor() : RGBAColor
    {
        $image = $this->duplicate();
        $image->resample(1, 1);
        
        return $image->getColorAt(0, 0);
    }
    
   /**
    * Calculates the image's average color value, and
    * returns an associative array with red, green,
    * blue and alpha keys.
    * 
    * @throws ImageHelper_Exception
    * @return RGBAColor
    */
    public function calcAverageColorRGB() : RGBAColor
    {
       return $this->calcAverageColor();
    }
    
   /**
    * Calculates the image's average color value, and
    * returns a hex color string (without the #).
    * 
    * @throws ImageHelper_Exception
    * @return string
    */
    public function calcAverageColorHex() : string
    {
        return $this->calcAverageColor()->toHEX();
    }
    
    /**
     * Retrieves the color value at the specified pixel
     * coordinates in the image.
     *
     * @param int $x
     * @param int $y
     * @return RGBAColor
     *
     * @throws ImageHelper_Exception
     * @see ImageHelper::ERROR_POSITION_OUT_OF_BOUNDS
     */
    public function getColorAt(int $x, int $y) : RGBAColor
    {
        if($x > $this->getWidth() || $y > $this->getHeight()) 
        {
            throw new ImageHelper_Exception(
                'Position out of bounds',
                sprintf(
                    'The position [%sx%s] does not exist in the image, it is [%sx%s] pixels in size.',
                    $x,
                    $y,
                    $this->getWidth(),
                    $this->getHeight()
                ),
                self::ERROR_POSITION_OUT_OF_BOUNDS
            );
        }
        
        $idx = imagecolorat($this->newImage, $x, $y);

        return $this->getIndexedColors($this->newImage, $idx);
    }
    
    /**
     * Retrieves the brightness of the image, in percent.
     *
     * @return float
     * @throws ImageHelper_Exception
     */
    public function getBrightness() : float
    {
        return $this->calcAverageColorRGB()->getBrightness();
    }
    
   /**
    * Retrieves a md5 hash of the source image file.
    * 
    * NOTE: Only works when the helper has been created
    * from a file. Otherwise, an exception is thrown.
    * 
    * @return string
    * @throws ImageHelper_Exception|OutputBuffering_Exception
    */
    public function getHash() : string
    {
        if($this->newImage === null)
        {
            throw new ImageHelper_Exception(
                'No image loaded to create a hash for.',
                'The newImage property is null.',
                self::ERROR_HASH_NO_IMAGE_LOADED
            );
        }

        OutputBuffering::start();
        imagepng($this->newImage);
        return md5(OutputBuffering::get());
    }
}
