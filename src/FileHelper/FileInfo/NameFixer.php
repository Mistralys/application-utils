<?php
/**
 * File containing the class {@see \AppUtils\FileHelper\FileInfo\NameFixer}.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\FileInfo\NameFixer
 */

declare(strict_types=1);

namespace AppUtils\FileHelper\FileInfo;

use AppUtils\FileHelper;

/**
 * Fixes common mistakes in file names entered by users,
 * mainly by removing unsupported whitespace and the like.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class NameFixer
{
    /**
     * Corrects common formatting mistakes when users enter
     * file names, like too many spaces, dots and the like.
     *
     * NOTE: if the file name contains a path, the path is
     * stripped, leaving only the file name.
     *
     * @param string $name
     * @return string
     */
    public static function fixName(string $name) : string
    {
        $name = trim($name);
        $name = FileHelper::normalizePath($name);
        $name = basename($name);

        $replaces = array(
            "\t" => ' ',
            "\r" => ' ',
            "\n" => ' ',
            ' .' => '.',
            '. ' => '.',
        );

        $name = str_replace(array_keys($replaces), array_values($replaces), $name);

        while(strpos($name, '  ') !== false) {
            $name = str_replace('  ', ' ', $name);
        }

        $name = str_replace(array_keys($replaces), array_values($replaces), $name);

        while(strpos($name, '..') !== false) {
            $name = str_replace('..', '.', $name);
        }

        return $name;
    }
}
