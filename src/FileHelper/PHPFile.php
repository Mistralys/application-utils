<?php
/**
 * @package Application Utils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\PHPFile
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\FileHelper_PHPClassInfo;
use SplFileInfo;

/**
 * Specialized file information class for PHP files.
 *
 * Create an instance with {@see PHPFile::factory()}.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class PHPFile extends FileInfo
{
    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return PHPFile
     * @throws FileHelper_Exception
     */
    public static function factory($path) : PHPFile
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
     * Validates a PHP file's syntax.
     *
     * NOTE: This will fail silently if the PHP command line
     * is not available. Use {@link FileHelper::canMakePHPCalls()}
     * to check this beforehand as needed.
     *
     * @return boolean|string[] A boolean true if the file is valid, an array with validation messages otherwise.
     * @throws FileHelper_Exception
     */
    public function checkSyntax()
    {
        if(!FileHelper::canMakePHPCalls())
        {
            return true;
        }

        $output = array();
        $command = sprintf('php -l "%s" 2>&1', $this->getPath());
        exec($command, $output);

        // when the validation is successful, the first entry
        // in the array contains the success message. When it
        // is invalid, the first entry is always empty.
        if(!empty($output[0])) {
            return true;
        }

        array_shift($output); // the first entry is always empty
        array_pop($output); // the last message is a superfluous message saying there's an error

        return $output;
    }

    public function findClasses() : FileHelper_PHPClassInfo
    {
        return new FileHelper_PHPClassInfo($this);
    }
}
