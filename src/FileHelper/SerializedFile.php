<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

class SerializedFile
{
    /**
     * @var FileInfo
     */
    private $file;

    private function __construct(FileInfo $file)
    {
        $this->file = $file;
    }

    public static function factory(FileInfo $file) : SerializedFile
    {
        return new SerializedFile($file);
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
        $contents = $this->file
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
                $this->file->getPath()
            ),
            FileHelper::ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED
        );
    }
}
