<?php

namespace AppUtils;

/**
 * Size container: instances of this class are returned when
 * using the {@link ImageHelper::getImageSize()} method, to
 * easily access the size information.
 *
 * @package Application Utils
 * @subpackage ImageHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see ImageHelper::getImageSize()
 */
class ImageHelper_Size implements \ArrayAccess
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