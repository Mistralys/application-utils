<?php
/**
 * File containing the class {@see \AppUtils\FileHelper\FileInfo\LineReader}.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\FileInfo\LineReader
 */

declare(strict_types=1);

namespace AppUtils\FileHelper\FileInfo;

use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_Exception;
use SplFileObject;

/**
 * Utility used to read contents from a file, line by line.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class LineReader
{
    /**
     * @var FileInfo
     */
    private FileInfo $file;

    public function __construct(FileInfo $file)
    {
        $this->file = $file;
    }

    /**
     * @param int $lineNumber
     * @return string|null
     * @throws FileHelper_Exception
     */
    public function getLine(int $lineNumber) : ?string
    {
        $this->file->requireExists();

        $file = new SplFileObject($this->file->getPath());

        if($file->eof()) {
            return '';
        }

        $targetLine = $lineNumber-1;

        $file->seek($targetLine);

        if($file->key() !== $targetLine)
        {
            return null;
        }

        return $file->current();
    }

    public function countLines() : int
    {
        $this->file->requireExists();
        $path = $this->file->getPath();

        $spl = new SplFileObject($path);

        // tries seeking as far as possible
        $spl->seek(PHP_INT_MAX);

        $number = $spl->key();

        // if seeking to the end the cursor is still at 0, there are no lines.
        if($number === 0)
        {
            // since it's a very small file, to get reliable results,
            // we read its contents and use that to determine what
            // kind of contents we are dealing with. Tests have shown
            // that this is not practical to solve with the SplFileObject.
            $content = file_get_contents($path);

            if(empty($content)) {
                return 0;
            }
        }

        // return the line number we were able to reach + 1 (key is zero-based)
        return $number+1;
    }

    public function getLines(int $amount=0) : array
    {
        $this->file->requireExists();

        $fn = fopen($this->file->getPath(), 'rb');

        if($fn === false)
        {
            throw new FileHelper_Exception(
                'Could not open file for reading.',
                sprintf(
                    'Tried accessing file at [%s].',
                    $this->file->getPath()
                ),
                FileHelper::ERROR_CANNOT_OPEN_FILE_TO_READ_LINES
            );
        }

        $result = array();
        $counter = 0;
        $first = true;

        while(!feof($fn))
        {
            $counter++;

            $line = fgets($fn);

            // can happen with zero length files
            if($line === false) {
                continue;
            }

            // the first line may contain a unicode BOM marker.
            if($first)
            {
                $line = ConvertHelper::stripUTFBom($line);
                $first = false;
            }

            $result[] = $line;

            if($amount > 0 && $counter === $amount) {
                break;
            }
        }

        fclose($fn);

        return $result;
    }
}
