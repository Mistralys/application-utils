<?php
/**
 * @package Application Utils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\SerializedFile
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use SplFileInfo;

/**
 * Handles files containing data serialized with the
 * PHP function {@see serialize()}.
 *
 * Create an instance with {@see SerializedFile::factory()}.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class SerializedFile extends FileInfo
{
    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return SerializedFile
     * @throws FileHelper_Exception
     */
    public static function factory($path) : SerializedFile
    {
        if($path instanceof self) {
            return $path;
        }

        $instance = self::createInstance($path);

        if($instance instanceof self) {
            return $instance;
        }

        throw new FileHelper_Exception(
            'Invalid class.'
        );
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
        $contents = $this
            ->requireExists()
            ->requireReadable()
            ->getContents();

        $result = @unserialize(
            $contents,
            array(
                'allowed_classes' => false
            )
        );

        if($result !== false) {
            return $result;
        }

        throw new FileHelper_Exception(
            'Cannot unserialize the file contents.',
            sprintf(
                'Tried unserializing the data from file at [%s].',
                $this->getPath()
            ),
            FileHelper::ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED
        );
    }

    /**
     * Saves the data serialized to the file.
     *
     * @param array<mixed> $data
     * @return $this
     * @throws FileHelper_Exception
     */
    public function putData(array $data) : self
    {
        $serialized = @serialize($data);

        return $this->putContents($serialized);
    }
}
