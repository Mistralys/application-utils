<?php
/**
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see \AppUtils\ConvertHelper\JSONConverter
 */

declare(strict_types=1);

namespace AppUtils\ConvertHelper;

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use JsonException;
use function AppUtils\parseVariable;

/**
 * Specialized converter for all things JSON encoding and decoding.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class JSONConverter
{
    public const JSON_CUT_LENGTH = 300;

    /**
     * Converts the specified variable to a JSON string.
     *
     * Works just like the native `json_encode` method,
     * except that it will trigger an exception on failure,
     * which has the json error details included in its
     * developer details.
     *
     * @param mixed $variable
     * @param int $options JSON encode options.
     * @param int $depth
     * @return string
     *
     * @throws JSONConverterException
     * @see ConvertHelper::ERROR_JSON_ENCODE_FAILED
     */
    public static function var2json($variable, int $options=0, int $depth=512) : string
    {
        try
        {
            return json_encode($variable, JSON_THROW_ON_ERROR | $options, $depth);
        }
        catch (JsonException $e)
        {
            throw new JSONConverterException(
                'Could not create json array'.json_last_error_msg(),
                sprintf(
                    'The call to json_encode failed for the variable [%s]. JSON error details: #%s, %s',
                    parseVariable($variable)->toString(),
                    $e->getCode(),
                    $e->getMessage()
                ),
                ConvertHelper::ERROR_JSON_ENCODE_FAILED,
                $e
            );
        }
    }

    /**
     * @param string $json
     * @param bool $assoc
     * @return mixed|NULL
     * @throws JSONConverterException
     */
    public static function json2var(string $json, bool $assoc=true)
    {
        if(empty($json)) {
            return null;
        }

        try
        {
            return json_decode($json, $assoc, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e)
        {
            throw new JSONConverterException(
                'Could not decode JSON string.',
                sprintf(
                    'The call to json_decode failed for the given string. '.PHP_EOL.
                    'JSON error details: #%s, %s'.PHP_EOL.
                    '(More details available via the previous exception)'.PHP_EOL.
                    'Source JSON string: '.PHP_EOL.
                    '%s',
                    $e->getCode(),
                    $e->getMessage(),
                    ConvertHelper::text_cut($json, self::JSON_CUT_LENGTH)
                ),
                ConvertHelper::ERROR_JSON_DECODE_FAILED,
                $e
            );
        }
    }

    /**
     * Attempts to convert a JSON string explicitly to
     * an associative array.
     *
     * @param array<mixed>|string $json Either a JSON-encoded string or an array,
     *                                  which will be passed through as-is, to
     *                                  avoid having to check if the string has
     *                                  already been decoded.
     * @return array<mixed>
     * @throws JSONConverterException
     */
    public static function json2array($json) : array
    {
        if(is_array($json)) {
            return $json;
        }

        $result = self::json2var($json);
        if(is_array($result)) {
            return $result;
        }

        throw new JSONConverterException(
            'JSON decoding did not return an array.',
            sprintf(
                'The string to decode returned a [%s]. '.PHP_EOL.
                'Source string: '.PHP_EOL.
                '%s',
                gettype($result),
                ConvertHelper::text_cut($json, self::JSON_CUT_LENGTH)
            ),
            ConvertHelper::ERROR_JSON_UNEXPECTED_DECODED_TYPE
        );
    }

    /**
     * Like {@see JSONConverter::json2array()}, but does not trigger an
     * exception on errors. It returns an empty array instead.
     *
     * @param array<mixed>|string $json Either a JSON-encoded string or an array,
     *                                  which will be passed through as-is, to
     *                                  avoid having to check if the string has
     *                                  already been decoded.
     * @return array<mixed>
     */
    public static function json2arraySilent($json) : array
    {
        try
        {
            return self::json2array($json);
        }
        catch (JSONConverterException $e)
        {
            return array();
        }
    }

    /**
     * Like {@see JSONConverter::var2json()}, but ignores any encoding
     * errors and returns an empty string instead.
     *
     * @param mixed $variable
     * @param int $options
     * @param int $depth
     * @return string
     */
    public static function var2jsonSilent($variable, int $options=0, int $depth=512) : string
    {
        try
        {
            return self::var2json($variable, $options, $depth);
        }
        catch (JSONConverterException $e)
        {
            return '';
        }
    }

    /**
     * Like {@see JSONConverter::json2var()}, but ignoring decoding
     * errors. Returns <code>NULL</code> instead in case of errors.
     *
     * @param string $json
     * @param bool $assoc
     * @return mixed|null
     */
    public static function json2varSilent(string $json, bool $assoc=true)
    {
        try
        {
            return self::json2var($json, $assoc);
        }
        catch (JSONConverterException $e)
        {
            return null;
        }
    }
}
