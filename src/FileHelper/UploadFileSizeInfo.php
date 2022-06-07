<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

class UploadFileSizeInfo
{
    private static int $max_size = -1;

    /**
     * Retrieves the maximum allowed upload file size, in bytes.
     * Takes into account the PHP ini settings <code>post_max_size</code>
     * and <code>upload_max_filesize</code>. Since these cannot
     * be modified at runtime, they are the hard limits for uploads.
     *
     * NOTE: Based on binary values, where 1KB = 1024 Bytes.
     *
     * @return int Will return <code>-1</code> if no limit.
     */
    public static function getFileSize() : int
    {
        if (self::$max_size < 0)
        {
            self::resolveSize();
        }

        return self::$max_size;
    }

    private static function resolveSize() : void
    {
        // Start with post_max_size.
        $post_max_size = self::parse_size(ini_get('post_max_size'));

        if ($post_max_size > 0) {
            self::$max_size = $post_max_size;
        }

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $upload_max = self::parse_size(ini_get('upload_max_filesize'));

        if ($upload_max > 0 && $upload_max < self::$max_size) {
            self::$max_size = $upload_max;
        }
    }

    private static function parse_size(string $size) : int
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $result = (float)preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.

        if($unit)
        {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return (int)round($result * (1024 ** stripos('bkmgtpezy', $unit[0])));
        }

        return (int)round($result);
    }
}
