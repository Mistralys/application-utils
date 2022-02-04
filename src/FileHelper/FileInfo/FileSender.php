<?php

declare(strict_types=1);

namespace AppUtils\FileHelper\FileInfo;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_Exception;

class FileSender
{
    /**
     * @var FileInfo
     */
    private $file;

    public function __construct(FileInfo $info)
    {
        $this->file = $info;
    }

    public function send(?string $fileName = null, bool $asAttachment=true) : void
    {
        $this->file
            ->requireExists()
            ->requireReadable();

        $this->sendHeaders(
            $this->resolveFileName($fileName),
            $asAttachment
        );

        readfile($this->file->getPath());
    }

    private function sendHeaders(string $fileName, bool $asAttachment) : void
    {
        header("Cache-Control: public", true);
        header("Content-Description: File Transfer", true);
        header("Content-Type: " . $this->detectMime(), true);

        header(sprintf(
            "Content-Disposition: %s; filename=%s",
            $this->resolveDisposition($asAttachment),
            '"'.$fileName.'"'
        ), true);
    }

    private function resolveFileName(?string $fileName) : string
    {
        return $fileName ?? $this->file->getName();
    }

    private function resolveDisposition(bool $asAttachment) : string
    {
        if($asAttachment)
        {
            return 'attachment';
        }

        return 'inline';
    }

    private function detectMime() : string
    {
        $mime = FileHelper::detectMimeType($this->file->getPath());
        if ($mime !== null)
        {
            return $mime;
        }

        throw new FileHelper_Exception(
            'Unknown file mime type',
            sprintf(
                'Could not determine mime type for file name [%s].',
                $this->file->getName()
            ),
            FileHelper::ERROR_UNKNOWN_FILE_MIME_TYPE
        );
    }
}
