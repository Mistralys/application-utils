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

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use JsonException;
use function AppUtils\sb;

/**
 * Specialized file handler for JSON encoded files.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class JSONFile
{
    /**
     * @var FileInfo
     */
    private FileInfo $file;

    /**
     * @var string
     */
    private string $targetEncoding = '';

    /**
     * @var string|string[]|NULL
     */
    private $sourceEncodings = '';

    private function __construct(FileInfo $file)
    {
        $this->file = $file;
    }

    public static function factory(FileInfo $file) : JSONFile
    {
        return new JSONFile($file);
    }

    /**
     * @param string $targetEncoding
     * @return JSONFile
     */
    public function setTargetEncoding(string $targetEncoding) : JSONFile
    {
        $this->targetEncoding = $targetEncoding;
        return $this;
    }

    /**
     * @param string|string[]|NULL $sourceEncodings
     * @return JSONFile
     */
    public function setSourceEncodings($sourceEncodings) : JSONFile
    {
        $this->sourceEncodings = $sourceEncodings;
        return $this;
    }

    /**
     * Opens a serialized file and returns the unserialized data.
     * Only supports serialized arrays - classes are not allowed.
     *
     * @throws FileHelper_Exception
     * @return array<int|string,mixed>
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
                        $this->file->getPath()
                    )
                    ->eol()
                    ->sf('Source encodings: [%s]', json_encode($this->sourceEncodings, JSON_THROW_ON_ERROR))
                    ->eol()
                    ->sf('Target encoding: [%s]', $this->targetEncoding),
                FileHelper::ERROR_CANNOT_DECODE_JSON_FILE,
                $e
            );
        }
    }

    private function getContents() : string
    {
        $contents = $this->file
            ->requireExists()
            ->requireReadable()
            ->getContents();

        return $this->convertEncoding($contents);
    }

    private function convertEncoding(string $contents) : string
    {
        if(!empty($this->targetEncoding))
        {
            return mb_convert_encoding(
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
    public function putData($data, bool $pretty) : JSONFile
    {
        $options = null;

        if($pretty)
        {
            $options = JSON_PRETTY_PRINT;
        }

        try
        {
            $json = json_encode($data, JSON_THROW_ON_ERROR | $options);

            $this->file->putContents($json);

            return $this;
        }
        catch (JsonException $e)
        {
            throw new FileHelper_Exception(
                'An error occurred while encoding a data set to JSON.',
                sprintf('Tried saving to file: [%s].', $this->file->getPath()),
                FileHelper::ERROR_JSON_ENCODE_ERROR,
                $e
            );
        }
    }
}
