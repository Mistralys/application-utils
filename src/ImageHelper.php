<?php
/**
 * File containing the {@link ImageHelper} class.
 * 
 * @package Helpers
 * @subpackage ImageHelper
 * @see ImageHelper
 */

/**
 * Image helper class that can be used to transform images,
 * and retrieve information about them.
 * 
 * @package Helpers
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @version 2.0
 */
class ImageHelper
{
    const ERROR_CANNOT_CREATE_IMAGE_CANVAS = 513001;
    
    const ERROR_IMAGE_FILE_DOES_NOT_EXIST = 513002;
    
    const ERROR_CANNOT_GET_IMAGE_SIZE = 513003;
    
    const ERROR_UNSUPPORTED_IMAGE_TYPE = 513004;
    
    const ERROR_FAILED_TO_CREATE_NEW_IMAGE = 513005;

    const ERROR_SAVE_NO_IMAGE_CREATED = 513006;
    
    const ERROR_CANNOT_WRITE_NEW_IMAGE_FILE = 513007;
    
    const ERROR_CREATED_AN_EMPTY_FILE = 513008;
    
    const ERROR_QUALITY_VALUE_BELOW_ZERO = 513009;
    
    const ERROR_QUALITY_ABOVE_ONE_HUNDRED = 513010;
    
    const ERROR_CANNOT_CREATE_IMAGE_OBJECT = 513011;
    
    const ERROR_CANNOT_COPY_RESAMPLED_IMAGE_DATA = 513012; 
    
    const ERROR_HEADERS_ALREADY_SENT = 513013;
    
    const ERROR_CANNOT_READ_SVG_IMAGE = 513014;
    
    const ERROR_SVG_SOURCE_VIEWBOX_MISSING = 513015;
    
    const ERROR_SVG_VIEWBOX_INVALID = 513016;
    
    const ERROR_NOT_A_RESOURCE = 513017;

    const ERROR_INVALID_STREAM_IMAGE_TYPE = 513018;

    const ERROR_NO_TRUE_TYPE_FONT_SET = 513019;
    
    const ERROR_POSITION_OUT_OF_BOUNDS = 513020;

    const ERROR_IMAGE_CREATION_FAILED = 513021;

    const ERROR_SAVE_IMAGE_RESOURCE_FALSE = 513022;
    
    const ERROR_CANNOT_CREATE_IMAGE_CROP = 513023;

   /**
    * @var string
    */
    protected $file;

   /**
    * @var ImageHelper_Size
    */
    protected $info;

   /**
    * @var string
    */
    protected $type;

   /**
    * @var resource|NULL
    */
    protected $newImage;

   /**
    * @var resource
    */
    protected $sourceImage;

   /**
    * @var int
    */
    protected $width;

   /**
    * @var int
    */
    protected $height;

   /**
    * @var int
    */
    protected $newWidth;

   /**
    * @var int
    */
    protected $newHeight;

   /**
    * @var int
    */
    protected $quality = 85;
    
    protected static $imageTypes = array(
        'png' => 'png',
        'jpg' => 'jpeg',
        'jpeg' => 'jpeg',
        'gif' => 'gif',
        'svg' => 'svg'
    );
    
    protected static $config = array(
        'auto-memory-adjustment' => true
    );

    protected $streamTypes = array(
        'jpeg',
        'png',
        'gif'
    );
    
    public function __construct($sourceFile=null, $resource=null, $type=null)
    {
        if(is_resource($resource)) 
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
    
            $this->type = self::getFileImageType($this->file);
            if (is_null($this->type)) {
                throw new ImageHelper_Exception(
                    'Error opening image',
                    'Not a valid supported image type for image ' . $this->file,
                    self::ERROR_UNSUPPORTED_IMAGE_TYPE
                );
            }

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
            $this->setNewImage($this->duplicateImage($this->sourceImage, true));
        }
    }

   /**
    * Factory method: creates a new helper with a blank image.
    * 
    * @param integer $width
    * @param integer $height
    * @param string $type The target file type when saving
    * @return ImageHelper
    */
    public static function createNew($width, $height, $type='png')
    {
        return self::createFromResource(imagecreatetruecolor($width, $height), 'png');
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
    * @param string $type The target image type, e.g. "jpeg", "png", etc.
    * @return ImageHelper
    */
    public static function createFromResource($resource, string $type)
    {
        self::requireResource($resource);
        
        return new ImageHelper(null, $resource, $type);
    }
    
   /**
    * Factory method: creates an image helper from an
    * image file on disk.
    *
    * @param string $path
    * @return ImageHelper
    */
    public static function createFromFile($file)
    {
        return new ImageHelper($file);
    }
    
   /**
    * Sets a global image helper configuration value. Available
    * configuration settings are:
    * 
    * <ul>
    * <li><code>auto-memory-adjustment</code> <i>boolean</i> Whether totry and adjust the memory limit automatically so there will be enough to load/process the target image.</li>
    * </ul>
    * 
    * @param string $name
    * @param mixed $value
    */
    public static function setConfig($name, $value)
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
    */
    public static function setAutoMemoryAdjustment($enabled=true)
    {
        self::setConfig('auto-memory-adjustment', $enabled);
    }
    
   /**
    * Duplicates an image resource.
    * @param resource $img
    * @return resource
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
    */
    public function duplicate()
    {
        return ImageHelper::createFromResource($this->duplicateImage($this->newImage));
    }

    public function enableAlpha()
    {
        if(!$this->alpha) 
        {
            self::addAlphaSupport($this->newImage, false);
            $this->alpha = true;
        }
        
        return $this;
    }
    
    public function resize($width, $height)
    {
        $new = $this->createNewImage($width, $height);
        
        imagecopy($new, $this->newImage, 0, 0, 0, 0, $width, $height);
        
        $this->setNewImage($new);
        
        return $this;
    }
    
    public function getNewSize()
    {
        return array($this->newWidth, $this->newHeight);
    }
    
    /**
     * Sharpens the image by the specified percentage.
     *
     * @param number $percent
     * @return ImageHelper
     */
    public function sharpen($percent=0)
    {
        if($percent <= 0) {
            return $this;
        }
        
        // the factor goes from 0 to 64 for sharpening.
        $factor = $percent * 64 / 100;
        return $this->convolute($factor);
    }
    
    public function blur($percent=0)
    {
        if($percent <= 0) {
            return $this;
        }
        
        // the factor goes from -64 to 0 for blurring.
        $factor = ($percent * 64 / 100) * -1;
        return $this->convolute($factor);
    }
    
    protected function convolute($factor)
    {
        // get a value thats equal to 64 - abs( factor )
        // ( using min/max to limited the factor to 0 - 64 to not get out of range values )
        $val1Adjustment = 64 - min( 64, max( 0, abs( $factor ) ) );
        
        // the base factor for the "current" pixel depends on if we are blurring or sharpening.
        // If we are blurring use 1, if sharpening use 9.
        $val1Base = 9;
        if( abs( $factor ) != $factor ) {
            $val1Base = 1;
        }
        
        // value for the center/currrent pixel is:
        //  1 + 0 - max blurring
        //  1 + 64- minimal blurring
        //  9 + 64- minimal sharpening
        //  9 + 0 - maximum sharpening
        $val1 = $val1Base + $val1Adjustment;
        
        // the value for the surrounding pixels is either positive or negative depending on if we are blurring or sharpening.
        $val2 = -1;
        if( abs( $factor ) != $factor ) {
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
    public function isTypeSVG()
    {
        return $this->type === 'svg';
    }
    
    /**
     * Whether the image is a PNG image.
     * @return boolean
     */
    public function isTypePNG()
    {
        return $this->type === 'png';
    }
    
    /**
     * Whether the image is a JPEG image.
     * @return boolean
     */
    public function isTypeJPEG()
    {
        return $this->type === 'jpeg';
    }
    
    /**
     * Whether the image is a vector image.
     * @return boolean
     */
    public function isVector()
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
     * @param integer $height
     * @return integer[]
     */
    public function getSizeByWidth($width) : ImageHelper_Size
    {
        $height = floor(($width * $this->height) / $this->width);
        
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
     * @return integer[]
     */
    public function getSizeByHeight($height) : ImageHelper_Size
    {
        $width = floor(($height * $this->width) / $this->height);
        
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
    */
    public function resampleByWidth($width)
    {
        $size = $this->getSizeByWidth($width);

        $this->resampleImage($size[0], $size[1]);
        
        return $this;
    }

   /**
    * Resamples the image by height, and creates a new image file on disk.
    * 
    * @param int $height
    * @return ImageHelper
    */
    public function resampleByHeight($height) : ImageHelper
    {
        $size = $this->getSizeByHeight($height);

        return $this->resampleImage($size[0], $size[1]);
    }

   /**
    * Resamples the image without keeping the aspect ratio.
    * 
    * @param int $width
    * @param int $height
    * @return ImageHelper
    */
    public function resample($width = null, $height = null) : ImageHelper
    {
        if($this->isVector()) {
            return $this;
        }
        
        if ($width == null && $height == null) {
            return $this->resampleByWidth($this->width);
        }

        if (empty($width)) {
            return $this->resampleByHeight($height);
        }

        if (empty($height)) {
            return $this->resampleByWidth($width);
        }

        return $this->resampleAndCrop($width, $height);
    }

    public function resampleAndCrop($width, $height) : ImageHelper
    {
        if($this->isVector()) {
            return $this;
        }

        if ($this->width <= $this->height) 
        {
            return $this->resampleByWidth($width);
        } 
        else 
        {
            return $this->resampleByHeight($height);
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
    
    protected $alpha = false;

   /**
    * Configures the specified image resource to make it alpha compatible.
    * 
    * @param resource $canvas
    * @param bool $fill Whether to fill the whole canvas with the transparency
    */
    public static function addAlphaSupport($canvas, $fill=true)
    {
        self::requireResource($canvas);
        
        imagealphablending($canvas,true);
        imagesavealpha($canvas, true);

        if($fill) {
            self::fillImageTransparent($canvas);
        }
    }
    
    public function isAlpha()
    {
        return $this->alpha;
    }

    public function save(string $targetFile, $dispose=true)
    {
        if($this->isVector()) {
            return true;
        }
        
        if (!isset($this->newImage)) {
            throw new ImageHelper_Exception(
                'Error creating new image',
                'Cannot save an image, no image was created. You have to call one of the resample methods to create a new image.',
                self::ERROR_SAVE_NO_IMAGE_CREATED
            );
        }

        if($this->newImage === false) {
            throw new ImageHelper_Exception(
                'Cannot save image, not a valid image resource',
                'The image is not a resource, but boolean false',
                self::ERROR_SAVE_IMAGE_RESOURCE_FALSE
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
                'Resampling completed sucessfully, but the generated file is 0 bytes big.',
                self::ERROR_CREATED_AN_EMPTY_FILE
            );
        }

        if($dispose) {
            $this->dispose();
        }
        
        return true;
    }
    
    public function dispose()
    {
        if(is_resource($this->sourceImage)) {
            imagedestroy($this->sourceImage);
        }
        
        if(is_resource($this->newImage)) {
            imagedestroy($this->newImage);
        }
    }

    protected function resolveQuality()
    {
        switch ($this->type) {
            case 'png':
                return 0;

            case 'jpeg':
                return $this->quality;

            default:
                return 0;
        }
    }

    /**
     * Sets the quality for image types like jpg that use compression.
     * @param int $quality
     */
    public function setQuality($quality)
    {
        $quality = $quality * 1;
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

        $this->quality = $quality * 1;
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

        //ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
        //default memory limit is 8MB so we will stick with that.
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

        if(is_null($newWidth)) { $newWidth = $this->newWidth; }
        if(is_null($newHeight)) { $newHeight = $this->newHeight; }
        
        if($this->newWidth==$newWidth && $this->newHeight==$newHeight) {
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
     * @param string $fileName
     * @return string|NULL
     * @see getImageType()
     */
    public static function getFileImageType($fileName)
    {
        return self::getImageType(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)));
    }

    /**
     * Gets the image type for the specified file extension,
     * or NULL if the extension is not among the supported
     * file types.
     *
     * @param string $extension
     * @return string|NULL
     */
    public static function getImageType($extension)
    {
        if (isset(self::$imageTypes[$extension])) {
            return self::$imageTypes[$extension];
        }

        return null;
    }

    public static function getImageTypes()
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
     */
    public static function displayImageStream($resource, $imageType, $quality=-1)
    {
        $imageType = strtolower($imageType);
        
        if(!in_array($imageType, self::$streamTypes)) {
            throw new ImageHelper_Exception(
                'Invalid image stream type',
                sprintf(
                    'The image type [%s] cannot be used for a stream.',
                    $imageType
                ),
                self::ERROR_INVALID_STREAM_IMAGE_TYPE
            );
        }
        
        header('Content-type:image/' . $imageType);

        $function = 'image' . $imageType;
        
        $function($resource, null, $quality);
        
        exit;
    }

    /**
     * Displays an image from an existing image file.
     * @param string $imageFile
     */
    public static function displayImage($imageFile)
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
        if($format == 'svg') {
            $format = 'svg+xml';
        }

        $contentType = 'image/' . $format;
        
        header('Content-Type: '.$contentType);
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($imageFile)) . " GMT");
        header('Cache-Control: public');
        header('Content-Length: ' . filesize($imageFile));

        readfile($imageFile);
        exit;
    }
    
   /**
    * Displays the current image.
    */
    public function display()
    {
        $this->displayImageStream($this->newImage, $this->getType(), $this->resolveQuality());
    }
    
   /**
    * Trims the current loaded image.
    * 
    * @param array $color A color definition, as an associative array with red, green, and blue keys. If not specified, the color at pixel position 0,0 will be used.
    */
    public function trim($color=null)
    {
        return $this->trimImage($this->newImage, $color);
    }
        
   /**
    * Trims the specified image resource by removing the specified color.
    * Also works with transparency.
    * 
    * @param resource $img
    * @param array $color A color definition, as an associative array with red, green, blue and alpha keys. If not specified, the color at pixel position 0,0 will be used.
    * @return ImageHelper
    */
    protected function trimImage($img, $color=null) : ImageHelper
    {
        if($this->isVector()) {
            return $this;
        }

        self::requireResource($img);
        
        if(empty($color)) {
            $color = imagecolorat($img, 0, 0);
            $color = imagecolorsforindex($img, $color);
        }
        
        // Get the image width and height.
        $imw = imagesx($img);
        $imh = imagesy($img);

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
                $ndx = imagecolorat($img, $ix, $iy);
                $colors = imagecolorsforindex($img, $ndx);
                
                if(!$this->colorsMatch($colors, $color)) 
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
        if($ymax === null && $ymax === null) {
            return $this;
        }
        
        // The new width and height of the image. 
        $imw = 1+$xmax-$xmin; // Image width in pixels
        $imh = 1+$ymax-$ymin; // Image height in pixels

        // Make another image to place the trimmed version in.
        $im2 = $this->createNewImage($imw, $imh);
        
        if($color['alpha'] > 0) 
        {
            $bg2 = imagecolorallocatealpha($im2, $color['red'], $color['green'], $color['blue'], $color['alpha']);
            imagecolortransparent($im2, $bg2);
        }
        else
        {
            $bg2 = imagecolorallocate($im2, $color['red'], $color['green'], $color['blue']);
        }
        
        // Make the background of the new image the same as the background of the old one.
        imagefill($im2, 0, 0, $bg2);

        // Copy it over to the new image.
        imagecopy($im2, $img, 0, 0, $xmin, $ymin, $imw, $imh);
        
        // To finish up, we replace the old image which is referenced.
        imagedestroy($img);
        
        $this->setNewImage($im2);

        return $this;
    }
    
   /**
    * Sets the new image after a transformation operation:
    * automatically adjusts the new size information.
    * 
    * @param resource $image
    */
    protected function setNewImage($image)
    {
        self::requireResource($image);
        
        $this->newImage = $image;
        $this->newWidth = imagesx($image);
        $this->newHeight= imagesy($image);
    }
    
   /**
    * Requires the subject to be a resource.
    * 
    * @param resource $subject
    * @throws ImageHelper_Exception
    */
    protected static function requireResource($subject)
    {
        if(is_resource($subject)) {
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
    protected function createNewImage(int $width, int $height)
    {
        $img = imagecreatetruecolor($width, $height);
        if (!$img) {
            throw new ImageHelper_Exception(
                'Error creating new image',
                'Cannot create new image canvas',
                self::ERROR_CANNOT_CREATE_IMAGE_CANVAS
            );
        }

        self::addAlphaSupport($img, true);
        
        return $img;
    }
    
   /**
    * Whether the two specified colors are the same.
    * 
    * @param array $a
    * @param array $b
    * @return boolean
    */
	protected function colorsMatch($a, $b) : bool
	{
		$parts = array('red', 'green', 'blue');
		foreach($parts as $part) {
			if($a[$part] != $b[$part]) {
				return false;
			}
		} 
		
		return true;
	}
	
	public function fillWhite($x=0, $y=0)
	{
	    $this->addRGBColor('white', 255, 255, 255);
        return $this->fill('white', $x, $y);
	}
	
	public function fillTransparent() : ImageHelper
	{
        $this->enableAlpha();
	    
	    self::fillImageTransparent($this->newImage);
	    
	    return $this;
	}
	
	public static function fillImageTransparent($resource)
	{
	    self::requireResource($resource);
	    
	    $transparent = imagecolorallocatealpha($resource, 89, 14, 207, 127);
	    imagecolortransparent ($resource, $transparent);
	    imagefill($resource, 0, 0, $transparent);
	}
	
	public function fill($colorName, $x=0, $y=0)
	{
	    imagefill($this->newImage, $x, $y, $this->colors[$colorName]);
	    return $this;
	}
	
    protected $colors = array();

    public function addRGBColor($name, $red, $green, $blue)
    {
        $this->colors[$name] = imagecolorallocate($this->newImage, $red, $green, $blue);
        return $this;
    }
    
    public function textTTF($text, $size, $colorName, $x=0, $y=0, $angle=0)
    {
        imagealphablending($this->newImage, true);
        
        imagettftext($this->newImage, $size, $angle, $x, $y, $this->colors[$colorName], $this->TTFFile, $text);
        
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
    
    public function paste(ImageHelper $target, $xpos=0, $ypos=0, $sourceX=0, $sourceY=0)
    {
        $img = $target->getImage();
        
        if($target->isAlpha()) {
            $this->enableAlpha();
        }
        
        imagecopy($this->newImage, $img, $xpos, $ypos, $sourceX, $sourceY, imagesx($img), imagesy($img));
        return $this;
    }
    
   /**
    * Retrieves the size of the image.
    * 
    * @param bool $exception Whether to trigger an exception when the image does not exist
    * @return ImageHelper_Size
    * @throws ImageHelper_Exception
    * @see ImageHelper::ERROR_CANNOT_GET_IMAGE_SIZE
    */
	public function getSize($exception=true) : ImageHelper_Size
    {
	    return self::getImageSize($this->newImage, $exception);
    }
    
    protected $TTFFile;
    
   /**
    * Sets the TTF font file to use for text operations.
    * 
    * @param string $filePath
    * @return ImageHelper
    */
    public function setFontTTF($filePath)
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
     * @return array
     */
    public function fitText($text, $matchWidth)
    {
        $sizes = array();
        for($i=1; $i<=1000; $i=$i+0.1) {
            $size = $this->calcTextSize($text, $i);
            $sizes[] = $size;
            if($size['width'] >= $matchWidth) {
                break;
            }
        }
    
        $last = array_pop($sizes);
        $prev = array_pop($sizes);
    
        // determine which is the closest match, and use that
        $diffLast = $last['width'] - $matchWidth;
        $diffPrev = $matchWidth - $prev['width'];
    
        if($diffLast <= $diffPrev) {
            return $last;
        }
    
        return $prev;
    }
    
    public function calcTextSize($text, $size)
    {
        $this->requireTTFFont();
        
        $box = imagettfbbox($size, 0, $this->TTFFile, $text);
    
        $left = $box[0];
        $right = $box[4];
        $bottom = $box[1];
        $top = $box[7];
    
        return array(
            'size' => $size,
            'top_left_x' => $box[6],
            'top_left_y' => $box[7],
            'top_right_x' => $box[4],
            'top_right_y' => $box[5],
            'bottom_left_x' => $box[0],
            'bottom_left_y' => $box[1],
            'bottom_right_x' => $box[2],
            'bottom_right_y' => $box[3],
            'width' => $right-$left,
            'height' => $bottom-$top
        );
    }
    
    protected function requireTTFFont()
    {
        if(isset($this->TTFFile)) {
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
	 * <pre>
	 * array(
	 *     0: (width),
	 *     1: (height),
	 *     "channels": the amount of channels
	 *     "bits": bits per channel
     * )     
	 * </pre>
	 *
	 * @param string|resource $pathOrResource
	 * @param bool $exception Whether to trigger an exception when the image does not exist
	 * @return ImageHelper_Size Size object, can also be accessed like the traditional array from getimagesize
	 * @see ImageHelper_Size
	 * @throws ImageHelper_Exception
	 * @see ImageHelper::ERROR_CANNOT_GET_IMAGE_SIZE
	 * @see ImageHelper::ERROR_CANNOT_READ_SVG_IMAGE
	 * @see ImageHelper::ERROR_SVG_SOURCE_VIEWBOX_MISSING
	 * @see ImageHelper::ERROR_SVG_VIEWBOX_INVALID
	 */
	public static function getImageSize($pathOrResource, $exception=true) : ImageHelper_Size
	{
	    if(is_resource($pathOrResource)) 
	    {
	        return new ImageHelper_Size(array(
	            'width' => imagesx($pathOrResource),
	            'height' => imagesy($pathOrResource),
	            'channels' => 1,
	            'bits' => 8
	        ));
	    }
	    
	    $type = self::getFileImageType($pathOrResource);
	    
	    $info = false;
	    $method = 'getImageSize_'.$type;
	    if(method_exists(__CLASS__, $method)) 
	    {
	        $info = call_user_func(array(__CLASS__, $method), $pathOrResource);
	    } 
	    else 
	    {
	        $info = getimagesize($pathOrResource);
	    }
	    
	    if($info === false) {
	        if($exception) {
    	        throw new ImageHelper_Exception(
    	            'Error opening image file',
    	            sprintf(
    	                'Could not get image size for image [%s]',
    	                $pathOrResource
	                ),
    	            self::ERROR_CANNOT_GET_IMAGE_SIZE
	            );
	        }
	        
	        return false;
	    }
	    
	    return new ImageHelper_Size($info);
	}
	
	protected static function getImageSize_svg($imagePath)
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
	    
	    if(!isset($data['@attributes']) || !isset($data['@attributes']['viewBox'])) {
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
	    $viewBox = explode(',', $viewBox);
	    if(count($viewBox) != 4) {
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
	    
	    $boxWidth = $viewBox[2];
	    $boxHeight = $viewBox[3];
	    
	    // calculate the x and y units of the document: 
	    // @see http://tutorials.jenkov.com/svg/svg-viewport-view-box.html#viewbox
	    //
	    // The viewbox combined with the width and heigt of the svg
	    // allow calculating how many pixels are in one unit of the 
	    // width and height of the document.
        //
	    $xUnits = $svgWidth / $boxWidth;
	    $yUnits = $svgHeight / $boxHeight;
	    
	    $pxWidth = $xUnits * $svgWidth;
	    $pxHeight = $yUnits * $svgHeight;
	    
	    return array(
	        $pxWidth,
	        $pxHeight,
	        'bits' => 8
	    );
	}
	
	/**
    * Crops the image to the specified width and height, optionally
    * specifying the origin position to crop from.
    * 
    * @param integer $width
    * @param integer $height
    * @param integer $x
    * @param integer $y
    * @return ImageHelper
    */
    public function crop($width, $height, $x=0, $y=0)
    {
        $new = $this->createNewImage($width, $height);
        
        imagecopy($new, $this->newImage, 0, 0, $x, $y, $width, $height);
        
        $this->setNewImage($new);
        
        return $this;
    }
    
    public function getWidth()
    {
        return $this->newWidth;
    }
    
    public function getHeight()
    {
        return $this->newHeight;
    }

   /**
    * Calculates the average color value used in 
    * the image. Returns an associative array
    * with the red, green, blue and alpha components.
    * 
    * @return array
    */
    public function calcAverageColor($format=self::COLORFORMAT_RGB)
    {
        $image = $this->duplicate();
        $image->resample(1, 1);
        return $image->getColorAt(0, 0, $format);
    }
    
    public static function rgb2hex($rgb)
    {
        return sprintf(
            "%02x%02x%02x",
            $rgb['red'],
            $rgb['green'],
            $rgb['blue']
        );
    }
    
    const COLORFORMAT_RGB = 1;
    
    const COLORFORMAT_HEX = 2;
    
   /**
    * Retrieves the color value at the specified pixel
    * coordinates in the image.
    * 
    * @param int $x
    * @param int $y
    * @return array
    */
    public function getColorAt($x, $y, $format=self::COLORFORMAT_RGB)
    {
        if($x > $this->getWidth() || $y > $this->getHeight()) {
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
        $rgb = imagecolorsforindex($this->newImage, $idx);
        
        if($format == self::COLORFORMAT_HEX) {
            return self::rgb2hex($rgb);
        }

        return $rgb;
    }
    
   /**
    * Converts an RGB value to its luminance equivalent.
    * 
    * @param array $rgb
    * @return integer Integer, from 0 to 255 (0=black, 255=white)
    */
    public static function rgb2luma($rgb)
    {
        return (($rgb['red']*2)+$rgb['blue']+($rgb['green']*3))/6;
    }
    
   /**
    * Retrieves the brightness of the image, in percent.
    * @return number
    */
    public function getBrightness()
    {
        $luma = self::rgb2luma($this->calcAverageColor());
        $percent = $luma * 100 / 255;
        return $percent;
    }
    
   /**
    * Retrieves an md5 hash of the source image file.
    * 
    * NOTE: Only works when the helper has been created
    * from a file. Otherwise an exception is thrown.
    * 
    * @return string
    * @throws ImageHelper_Exception
    */
    public function getHash()
    {
        ob_start();
        imagepng($this->newImage);
        $md5 = md5(ob_get_clean());
        
        return $md5;
    }
}

/**
 * Custom exception class for the Image helper.
 * Allows setting and getting additional developer-
 * only error details, when available.
 * 
 * @package Helpers
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ImageHelper_Exception extends Exception
{
    protected $details;
    
    public function __construct($message, $details=null, $code=null, $previous=null)
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }
    
    public function getDetails()
    {
        return $this->details;
    }
}

/**
 * Size container: instances of this class are returned when
 * using the {@link ImageHelper::getImageSize()} method, to
 * easily access the size information.
 *
 * @package Helpers
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see ImageHelper::getImageSize()
 */
class ImageHelper_Size implements ArrayAccess
{
    protected $size;
    
    public function __construct(array $size)
    {
        if(!isset($size['width'])) {
            $size['width'] = $size[0];
        }
        
        if(!isset($size['height'])) {
            $size['height'] = $size[1];
        }
        
        if(!isset($size[0])) {
            $size[0] = $size['width'];
        }
        
        if(!isset($size[1])) {
            $size[1] = $size['height'];
        }
        
        if(!isset($size['channels'])) {
            $size['channels'] = 1;
        }
        
        $this->size = $size;
    }
    
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
    
    public function offsetExists($offset)
    {
        return isset($this->size[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if(isset($this->size[$offset])) {
            return $this->size[$offset];
        }
        
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        if(is_null($offset)) {
            $this->size[] = $value;
        } else {
            $this->size[$offset] = $value; 
        }
    }
    
    public function offsetUnset($offset) 
    {
        unset($this->size[$offset]);
    }
}