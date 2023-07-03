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
use AppUtils\Interface_Stringable;
use SplFileInfo;

/**
 * Interface for the file and folder info classes.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface PathInfoInterface extends Interface_Stringable
{
    public function getName() : string;
    public function getExtension(bool $lowercase=true) : string;
    public function getPath() : string;
    public function getFolderPath() : string;

    /**
     * @return int The size on disk, in bytes.
     */
    public function getSize() : int;
    public function exists() : bool;
    public function isFolder() : bool;
    public function isFile() : bool;
    public function isIndeterminate() : bool;
    public function isWritable() : bool;
    public function isReadable() : bool;

    /**
     * Whether this file or folder's path is located within the target path:
     * Returns true if the path equals to the target path, or is a subfolder
     * of the target path.
     *
     * Examples, assuming this is a folder with the following path:
     *
     * <code>/home/user/someone</code>
     *
     * Checking target paths against this:
     *
     * - /home/user/someone = true (same path)
     * - /home/user/someone/readme.txt = true (file within the path)
     * - /home/user/someone/subfolder = true (subfolder of the path)
     * - /path/to/folder = false (different path)
     * - /home/user = false (parent folder of the path)
     *
     * NOTE: Both paths must exist on disk to resolve relative
     * paths with <code>../</code> parts.
     *
     * @param string|PathInfoInterface|SplFileInfo $targetPath
     * @return bool
     */
    public function isWithinPath($targetPath) : bool;

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
