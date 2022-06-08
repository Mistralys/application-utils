<?php
/**
 * File containing the interface {@see \AppUtils\FileHelper\PathInfoInterface}.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\PathInfoInterface
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Interface for the file and folder info classes.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface PathInfoInterface
{
    public function getName() : string;
    public function getExtension(bool $lowercase=true) : string;
    public function getPath() : string;
    public function exists() : bool;
    public function isFolder() : bool;
    public function isFile() : bool;
    public function isWritable() : bool;
    public function isReadable() : bool;

    /**
     * @return $this
     * @throws FileHelper_Exception
     */
    public function requireExists() : self;

    /**
     * @return $this
     * @throws FileHelper_Exception
     */
    public function requireReadable() : self;

    /**
     * @return $this
     * @throws FileHelper_Exception
     */
    public function requireWritable() : self;

    /**
     * @return FolderInfo
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
     */
    public function requireIsFolder() : FolderInfo;

    /**
     * @return FileInfo
     * @throws FileHelper_Exception
     */
    public function requireIsFile() : FileInfo;

    /**
     * @return $this
     * @throws FileHelper_Exception
     */
    public function delete() : self;
}
