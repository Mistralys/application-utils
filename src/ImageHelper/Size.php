<?php

declare(strict_types=1);

namespace AppUtils;

use ArrayAccess;

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
 */
class ImageHelper_Size implements ArrayAccess
{
    /**
     * @var array{width:int,height:int,channels:int,bits:int,0:int,1:int}
     */
    protected array $size;

    /**
     * @param array<string|int,int> $size
     */
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

        if(!isset($size['bits'])) {
            $size['bits'] = -1;
        }
        
        $this->size = $size;
    }

    /**
     * @param array<string|int,int>|ImageHelper_Size $arrayOrInstance
     * @return ImageHelper_Size
     */
    public static function create($arrayOrInstance) : ImageHelper_Size
    {
        if($arrayOrInstance instanceof self) {
            return new self($arrayOrInstance->toArray());
        }

        return new self($arrayOrInstance);
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

    /**
     * @return array{width:int,height:int,channels:int,bits:int,0:int,1:int}
     */
    public function toArray() : array
    {
        return $this->size;
    }

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
}
