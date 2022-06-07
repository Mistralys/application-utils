<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;

class PathRelativizer
{
    /**
     * Makes a path relative using a folder depth: will reduce the
     * length of the path so that only the amount of folders defined
     * in the <code>$depth</code> attribute are shown below the actual
     * folder or file in the path.
     *
     * @param string  $path The absolute or relative path
     * @param int $depth The folder depth to reduce the path to
     * @return string
     */
    public static function relativizeByDepth(string $path, int $depth=2) : string
    {
        $path = FileHelper::normalizePath($path);

        $tokens = explode('/', $path);
        $tokens = array_filter($tokens); // remove empty entries (trailing slash for example)
        $tokens = array_values($tokens); // re-index keys

        if(empty($tokens)) {
            return '';
        }

        // remove the drive if present
        if(strpos($tokens[0], ':') !== false) {
            array_shift($tokens);
        }

        // path was only the drive
        if(count($tokens) === 0) {
            return '';
        }

        // the last element (file or folder)
        $target = array_pop($tokens);

        // reduce the path to the specified depth
        $length = count($tokens);
        if($length > $depth) {
            $tokens = array_slice($tokens, $length-$depth);
        }

        // append the last element again
        $tokens[] = $target;

        return trim(implode('/', $tokens), '/');
    }

    /**
     * Makes the specified path relative to another path,
     * by removing one from the other if found. Also
     * normalizes the path to use forward slashes.
     *
     * Example:
     *
     * <pre>
     * relativizePath('c:\some\folder\to\file.txt', 'c:\some\folder');
     * </pre>
     *
     * Result: <code>to/file.txt</code>
     *
     * @param string $path
     * @param string $relativeTo
     * @return string
     */
    public static function relativize(string $path, string $relativeTo) : string
    {
        $path = FileHelper::normalizePath($path);
        $relativeTo = FileHelper::normalizePath($relativeTo);

        $relative = str_replace($relativeTo, '', $path);

        return trim($relative, '/');
    }
}
