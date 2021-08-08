<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_Array
{
    /**
     * Removes the specified keys from the target array,
     * if they exist.
     *
     * @param array<number|string,mixed> $sourceArray
     * @param string[] $keys
     */
    public static function removeKeys(array &$sourceArray, array $keys) : void
    {
        foreach($keys as $key)
        {
            if(array_key_exists($key, $sourceArray)) {
                unset($sourceArray[$key]);
            }
        }
    }

    /**
     * Removes the target values from the source array.
     *
     * @param array<number|string,mixed> $sourceArray The indexed or associative array
     * @param array<number|string,mixed> $values Indexed list of values to remove
     * @param bool $keepKeys Whether to maintain index association
     * @return array<number|string,mixed>
     */
    public static function removeValues(array $sourceArray, array $values, bool $keepKeys=false) : array
    {
        $result = array();
        $values = array_values($values);

        foreach($sourceArray as $key => $value)
        {
            if(in_array($value, $values, true)) {
                continue;
            }

            if($keepKeys) {
                $result[$key] = $value;
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * Removes values from the target array, while maintaining index
     * association. Returns the modified array.
     *
     * @param array<number|string,mixed> $sourceArray
     * @param array<number|string,mixed> $values
     * @return array<number|string,mixed>
     */
    public static function removeValuesAssoc(array $sourceArray, array $values) : array
    {
        return self::removeValues($sourceArray, $values, true);
    }

    /**
     * Converts an associative array to an HTML style attribute value string.
     *
     * @param array<string,mixed> $subject
     * @return string
     */
    public static function toStyleString(array $subject) : string
    {
        $tokens = array();
        foreach($subject as $name => $value) {
            $tokens[] = $name.':'.strval($value);
        }

        return implode(';', $tokens);
    }

    /**
     * Converts an associative array with attribute name > value pairs
     * to an attribute string that can be used in an HTML tag. Empty
     * attribute values are ignored.
     *
     * Example:
     *
     * array2attributeString(array(
     *     'id' => 45,
     *     'href' => 'http://www.mistralys.com'
     * ));
     *
     * Result:
     *
     * id="45" href="http://www.mistralys.com"
     *
     * @param array<string,mixed> $array
     * @return string
     */
    public static function toAttributeString(array $array) : string
    {
        $tokens = array();
        foreach($array as $attr => $value)
        {
            $value = strval($value);

            if($value === '') {
                continue;
            }

            $tokens[] = $attr.'="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"';
        }

        if(empty($tokens)) {
            return '';
        }

        return ' '.implode(' ', $tokens);
    }

    /**
     * Implodes an array with a separator character, and the last item with "and".
     *
     * By default, this will create the following result:
     *
     * array('One', 'two', 'three') = "One, two and three"
     *
     * @param array<int,mixed> $list The indexed array with items to implode.
     * @param string $sep The separator character to use.
     * @param string $conjunction The word to use as conjunction with the last item in the list. NOTE: include spaces as needed.
     * @return string
     */
    public static function implodeWithAnd(array $list, string $sep = ', ', string $conjunction = '') : string
    {
        if(empty($list)) {
            return '';
        }

        if(empty($conjunction)) {
            $conjunction = ' '.t('and').' ';
        }

        $last = array_pop($list);
        if($list) {
            return implode($sep, $list) . $conjunction . $last;
        }

        return $last;
    }
}
