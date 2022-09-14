<?php
/**
 * File containing the class {@see \AppUtils\FileHelper\JSONFile}.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\JSONFile
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\ClassHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use JsonException;
use SplFileInfo;
use function AppUtils\sb;

/**
 * Specialized file handler for JSON encoded files.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class JSONFile extends FileInfo
{
    /**
     * @var string
     */
    private string $targetEncoding = '';

    /**
     * @var string|string[]|NULL
     */
    private $sourceEncodings = '';

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return JSONFile
     * @throws FileHelper_Exception
     */
    public static function factory($path) : JSONFile
    {
        return ClassHelper::requireObjectInstanceOf(
            self::class,
            self::createInstance($path)
        );
    }

    /**
     * @param string $targetEncoding
     * @return $this
     */
    public function setTargetEncoding(string $targetEncoding) : self
    {
        $this->targetEncoding = $targetEncoding;
        return $this;
    }

    /**
     * @param string|string[]|NULL $sourceEncodings
     * @return $this
     */
    public function setSourceEncodings($sourceEncodings) : self
    {
        $this->sourceEncodings = $sourceEncodings;
        return $this;
    }

    /**
     * Opens a serialized file and returns the unserialized data.
     * Only supports serialized arrays - classes are not allowed.
     *
     * @return array<int|string,mixed>
     * @throws FileHelper_Exception
     * @throws JsonException
     * @see FileHelper::parseSerializedFile()
     *
     * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
     * @see FileHelper::ERROR_SERIALIZED_FILE_CANNOT_BE_READ
     * @see FileHelper::ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED
     */
    public function parse() : array
    {
        try
        {
            return json_decode(
                $this->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException $e)
        {
            throw new FileHelper_Exception(
                'Cannot decode json data',
                (string)sb()
                    ->sf(
                        'Loaded the contents of file [%s] successfully, but decoding it as JSON failed.',
                        $this->getPath()
                    )
                    ->eol()
                    ->sf('Source encodings: [%s]', JSONConverter::var2jsonSilent($this->sourceEncodings))
                    ->eol()
                    ->sf('Target encoding: [%s]', $this->targetEncoding),
                FileHelper::ERROR_CANNOT_DECODE_JSON_FILE,
                $e
            );
        }
    }

    public function getContents() : string
    {
        return $this->convertEncoding(parent::getContents());
    }

    private function convertEncoding(string $contents) : string
    {
        if(!empty($this->targetEncoding))
        {
            return (string)mb_convert_encoding(
                $contents,
                $this->targetEncoding,
                $this->sourceEncodings
            );
        }

        return $contents;
    }

    /**
     * @param mixed $data
     * @param bool $pretty
     * @return $this
     * @throws FileHelper_Exception
     */
    public function putData($data, bool $pretty) : self
    {
        $options = null;

        if($pretty)
        {
            $options = JSON_PRETTY_PRINT;
        }

        try
        {
            $json = JSONConverter::var2json($data, $options);

            $this->putContents($json);

            return $this;
        }
        catch (JSONConverterException $e)
        {
            throw new FileHelper_Exception(
                'An error occurred while encoding a data set to JSON.',
                sprintf('Tried saving to file: [%s].', $this->getPath()),
                FileHelper::ERROR_JSON_ENCODE_ERROR,
                $e
            );
        }
    }
}
