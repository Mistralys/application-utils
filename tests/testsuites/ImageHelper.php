<?php

use PHPUnit\Framework\TestCase;

use AppUtils\ImageHelper;

final class ImageHelperTest extends TestCase
{
    protected $testImages = array(
        array(
            'file' => 'test-image.jpg',
            'type' => 'jpeg',
            'size'  => array(272, 158),
            'isVector' => false,
            'transparency' => false,
            'bits' => 8,
            'resample' => array(100, 58)
        ),
        array(
            'file' => 'test-image-8-bit.png',
            'type' => 'png',
            'size'  => array(272, 158),
            'isVector' => false,
            'transparency' => false,
            'bits' => 8,
            'resample' => array(100, 58)
        ),
        array(
            'file' => 'test-image-8-bit-transparent.png',
            'type' => 'png',
            'size'  => array(272, 158),
            'isVector' => false,
            'transparency' => true,
            'bits' => 8
        ),
        array(
            'file' => 'test-image-24-bit.png',
            'type' => 'png',
            'size'  => array(272, 158),
            'isVector' => false,
            'transparency' => false,
            'bits' => 8
        ),
        array(
            'file' => 'test-image-24-bit-transparent.png',
            'type' => 'png',
            'size'  => array(272, 158),
            'isVector' => false,
            'transparency' => true,
            'bits' => 8
        ),
        array(
            'file' => 'test-image.svg',
            'type' => 'svg',
            'size'  => array(210, 297),
            'isVector' => true,
            'transparency' => false,
            'bits' => 8
        ),
    );
    
    protected $dataPath;
    
    protected function setUp() : void
    {
        if(isset($this->dataPath)) {
            return;
        }
        
        $this->dataPath = TESTS_ROOT.'/assets/ImageHelper';
        
        foreach($this->testImages as $idx => $entry)
        {
            $this->testImages[$idx]['path'] = $this->dataPath.'/'.$entry['file'];
        }
    }
    
    /**
     * @see ImageHelper::getImageSize()
     */
    public function test_getImageSize()
    {
        foreach($this->testImages as $entry) 
        {
            $file = $entry['path'];
            $size = ImageHelper::getImageSize($file);
            $type = strtoupper($entry['type']);
            
            $this->assertEquals($entry['size'][0], $size[0], $type.' width does not match in '.$entry['file']);
            $this->assertEquals($entry['size'][1], $size[1], $type.' height does not match in '.$entry['file']);
            $this->assertEquals($entry['bits'], $size['bits'], $type.' bit depth does not match in '.$entry['file']);
            
            $helper = ImageHelper::createFromFile($file);
            
            $this->assertEquals($entry['isVector'], $helper->isVector());
            $this->assertEquals($entry['type'], ImageHelper::getFileImageType($file));
            
            $helper->dispose();
        }
    }
    
    public function test_getSizeByWidth()
    {
        foreach($this->testImages as $entry) 
        {
            if(!isset($entry['resample'])) {
                continue;
            }
            
            $image = ImageHelper::createFromFile($entry['path']);
            $image->resampleByWidth($entry['resample'][0]);
            
            $size = $image->getSize();
            
            $this->assertEquals($entry['resample'][0], $size->getWidth());
            $this->assertEquals($entry['resample'][1], $size->getHeight());
        }
    }
}