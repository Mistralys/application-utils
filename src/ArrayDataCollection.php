<?php
/**
 * @package Application Utils
 * @subpackage Collections
 * @see \AppUtils\ArrayDataCollection
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\ArrayDataCollection\ArrayDataCollectionException;
use JsonException;
use testsuites\Traits\RenderableTests;

/**
 * Collection class used to work with associative arrays used to
 * store key => value pairs.
 *
 * Offers strict typed methods to access the available keys, to
 * remove the hassle of checking whether keys exist, and whether
 * they are of the expected type.
 *
 * @package Application Utils
 * @subpackage Collections
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ArrayDataCollection
{
    public const ERROR_JSON_DECODE_FAILED = 116001;

    /**
     * @var array<string,mixed>
     */
    protected array $data;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data=array())
    {
        $this->data = $data;
    }

    /**
     * @param ArrayDataCollection|array<string,mixed>|NULL $data
     * @return ArrayDataCollection
     */
    public static function create($data=array()) : ArrayDataCollection
    {
        if($data instanceof self) {
            return $data;
        }

        return new ArrayDataCollection($data);
    }

    /**
     * @return array<string,mixed>
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param array<string,mixed> $data
     * @return $this
     */
    public function setKeys(array $data) : self
    {
        foreach($data as $key => $value)
        {
            $this->setKey($key, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed|NULL $value
     * @return $this
     */
    public function setKey(string $name, $value) : self
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Merges the current collection's data with that of
     * the target collection, replacing existing values.
     *
     * @param ArrayDataCollection $collection
     * @return $this
     */
    public function mergeWith(ArrayDataCollection $collection) : self
    {
        return $this->setKeys($collection->getData());
    }

    /**
     * Combines the current collection's data with the
     * target collection, and returns a new collection
     * that contains the data of both collections.
     *
     * NOTE: The source collection's values are overwritten
     * by the target collection in the process.
     *
     * @param ArrayDataCollection $collection
     * @return ArrayDataCollection
     */
    public function combine(ArrayDataCollection $collection) : ArrayDataCollection
    {
        return self::create($this->data)->setKeys($collection->getData());
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getKey(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * The stored value can be a string or a number.
     * All other types and values will return an empty
     * string.
     *
     * @param string $name
     * @return string
     */
    public function getString(string $name) : string
    {
        $value = $this->getKey($name);

        if(is_string($value)) {
            return $value;
        }

        if(is_numeric($value)) {
            return (string)$value;
        }

        return '';
    }

    /**
     * The stored value can be an integer or float,
     * or a string containing an integer or float.
     * All other types and values return <code>0</code>.
     *
     * @param string $name
     * @return int
     */
    public function getInt(string $name) : int
    {
        $value = $this->getKey($name);

        if(is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * Attempts to decode the stored string as JSON.
     *
     * NOTE: Only JSON that decodes into an array is
     * accepted. Other values, like booleans or numbers,
     * will return an empty array.
     *
     * @param string $name
     * @return array<mixed>
     * @throws ArrayDataCollectionException
     */
    public function getJSONArray(string $name) : array
    {
        $value = $this->getString($name);

        if(empty($value)) {
            return array();
        }

        try
        {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            if(is_array($value)) {
                return $value;
            }

            return array();
        }
        catch (JsonException $e)
        {
            throw new ArrayDataCollectionException(
                'Invalid JSON encountered in array data collection.',
                sprintf(
                    'The JSON string could not be decoded.'.PHP_EOL.
                    'Reason: %s'.PHP_EOL.
                    'Raw JSON given:'.PHP_EOL.
                    '---------------------------------------'.PHP_EOL.
                    '%s'.PHP_EOL.
                    '---------------------------------------'.PHP_EOL,
                    $e->getMessage(),
                    ConvertHelper::text_cut($value, 500)
                ),
                self::ERROR_JSON_DECODE_FAILED,
                $e
            );
        }
    }

    public function getArray(string $name) : array
    {
        $value = $this->getKey($name);

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    public function getBool(string $name) : bool
    {
        $value = $this->getKey($name);

        if(is_string($value)) {
            $value = strtolower($value);
        }

        return
            $value === true
            ||
            $value === 'true'
            ||
            $value === 'yes'
            ||
            $value === 1;
    }

    public function getFloat(string $name) : float
    {
        $value = $this->getKey($name);

        if(is_numeric($value)) {
            return (float)$value;
        }

        return 0.0;
    }

    /**
     * Whether the specified key exists in the data set,
     * even if its value is <code>NULL</code>.
     *
     * @param string $name
     * @return bool
     */
    public function keyExists(string $name) : bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Whether the specified key exists in the data set,
     * and has a non-<code>NULL</code> value.
     *
     * @param string $name
     * @return bool
     */
    public function keyHasValue(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Removes the specified key from the data set, if it exists.
     *
     * @param string $name
     * @return $this
     */
    public function removeKey(string $name) : self
    {
        unset($this->data[$name]);
        return $this;
    }
}
