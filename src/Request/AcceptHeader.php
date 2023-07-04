<?php
/**
 * @package Application Utils
 * @subpackage Request
 * @see \AppUtils\Request\AcceptHeader
 */

declare(strict_types=1);

namespace AppUtils\Request;

use ArrayAccess;
use ReturnTypeWillChange;

/**
 * Holds the information on an "Accept" header.
 * Can be accessed like an array.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,mixed>
 */
class AcceptHeader implements ArrayAccess
{
    /**
     * @var array{type:string,pos:int,quality:float,params:array<string,string>}
     */
    private array $data;

    /**
     * @param string $type
     * @param int $position
     * @param array<string,string> $parameters
     * @param float $quality
     */
    public function __construct(string $type, int $position, array $parameters, float $quality)
    {
        $this->data = array(
            'type' => $type,
            'pos' => $position,
            'params' => $parameters,
            'quality' => $quality
        );
    }

    public function getMimeType() : string
    {
        return $this->data['type'];
    }

    public function getPosition() : int
    {
        return $this->data['pos'];
    }

    /**
     * @return array<string,string>
     */
    public function getParameters() : array
    {
        return $this->data['params'];
    }

    public function getQuality() : float
    {
        return $this->data['quality'];
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return array<string,string>|int|float|string
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        if(isset($this->data[$offset]) && gettype($this->data[$offset]) === gettype($value)) {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        // nope
    }
}
