<?php
/**
 * File containing the {@see \AppUtils\FileHelper\PathsReducer} class.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\PathsReducer
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Takes a list of file or folder paths, and attempts to reduce
 * them to the closest common relative path.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see FileHelper::createPathsReducer()
 */
class PathsReducer
{
    /**
     * @var string[]
     */
    private array $paths = array();

    /**
     * @param string[] $paths
     * @throws FileHelper_Exception
     */
    public function __construct(array $paths=array())
    {
        $this->addPaths($paths);
    }

    /**
     * Adds a list of paths to reduce.
     *
     * @param string[] $paths
     * @return $this
     */
    public function addPaths(array $paths) : PathsReducer
    {
        foreach($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * Adds a single path to the reducer.
     *
     * @param string $path
     * @return $this
     */
    public function addPath(string $path) : PathsReducer
    {
        $path = FileHelper::normalizePath($path);

        if(!in_array($path, $this->paths, true)) {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * Analyzes the paths and returns them reduced
     * if at all possible.
     *
     * @return string[]
     */
    public function reduce() : array
    {
        $split = $this->splitPaths();

        if(empty($split)) {
            return array();
        }

        while($this->shiftPart($split) === true) {}

        return $this->joinPaths($split);
    }

    /**
     * @param array<int,array<int,string>> $split
     * @return string[]
     */
    private function joinPaths(array $split) : array
    {
        $result = array();

        foreach ($split as $entry) {
            if(!empty($entry)) {
                $result[] = implode('/', $entry);
            }
        }

        return $result;
    }

    /**
     * @param array<int,array<int,string>> $split
     * @return bool
     */
    private function shiftPart(array &$split) : bool
    {
        $current = null;
        $result = array();

        foreach($split as $entry)
        {
            if(empty($entry)) {
                return false;
            }

            $part = array_shift($entry);
            if(empty($entry)) {
                return false;
            }

            if($current === null) {
                $current = $part;
            }

            if($part !== $current) {
                return false;
            }

            $result[] = $entry;
        }

        $split = $result;

        return true;
    }

    /**
     * @return array<int,array<int,string>>
     */
    private function splitPaths() : array
    {
        $split = array();

        foreach($this->paths as $path) {
            $entry = ConvertHelper::explodeTrim('/', $path);
            if(!empty($entry)) {
                $split[] = $entry;
            }
        }

        return $split;
    }
}
