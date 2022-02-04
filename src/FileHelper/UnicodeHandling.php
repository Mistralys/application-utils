<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

class UnicodeHandling
{
    /**
     * @var array<string,string>|NULL
     */
    protected static $utfBoms;

    /**
     * @var string[]|NULL
     */
    protected static $encodings;

    public function __construct()
    {
        $this->initBOMs();
        $this->initEncodings();
    }

    /**
     * Detects the UTF BOM in the target file, if any. Returns
     * the encoding matching the BOM, which can be any of the
     * following:
     *
     * <ul>
     * <li>UTF32-BE</li>
     * <li>UTF32-LE</li>
     * <li>UTF16-BE</li>
     * <li>UTF16-LE</li>
     * <li>UTF8</li>
     * </ul>
     *
     * @param FileInfo $file
     * @return string|NULL
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_OPEN_FILE_TO_DETECT_BOM
     */
    public function detectUTFBom(FileInfo $file) : ?string
    {
        $file
            ->requireExists(FileHelper::ERROR_CANNOT_OPEN_FILE_TO_DETECT_BOM)
            ->requireReadable(FileHelper::ERROR_CANNOT_OPEN_FILE_TO_DETECT_BOM);

        $fp = fopen($file->getPath(), 'rb');

        $text = fread($fp, 20);

        fclose($fp);

        foreach(self::$utfBoms as $bom => $value)
        {
            if(mb_strpos($text, $value) === 0)
            {
                return $bom;
            }
        }

        return null;
    }

    private function initBOMs() : void
    {
        if(isset(self::$utfBoms))
        {
            return;
        }

        self::$utfBoms = array(
            'UTF32-BE' => chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF),
            'UTF32-LE' => chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00),
            'UTF16-BE' => chr(0xFE) . chr(0xFF),
            'UTF16-LE' => chr(0xFF) . chr(0xFE),
            'UTF8' => chr(0xEF) . chr(0xBB) . chr(0xBF)
        );
    }

    private function initEncodings() : void
    {
        if(isset(self::$encodings))
        {
            return;
        }

        $encodings = $this->getKnownUnicodeEncodings();

        self::$encodings = array();

        foreach($encodings as $string)
        {
            $withHyphen = str_replace('UTF', 'UTF-', $string);

            self::$encodings[] = $string;
            self::$encodings[] = $withHyphen;
            self::$encodings[] = str_replace(array('-BE', '-LE'), '', $string);
            self::$encodings[] = str_replace(array('-BE', '-LE'), '', $withHyphen);
        }
    }

    /**
     * Retrieves a list of all UTF byte order mark character
     * sequences, as an associative array with
     * UTF encoding => bom sequence pairs.
     *
     * @return array<string,string>
     */
    public function getUTFBOMs() : array
    {
        return self::$utfBoms;
    }

    /**
     * Checks whether the specified encoding is a valid
     * unicode encoding, for example "UTF16-LE" or "UTF8".
     * Also accounts for alternate way to write them, like
     * "UTF-8", and omitting little/big endian suffixes.
     *
     * @param string $encoding
     * @return boolean
     */
    public function isValidUnicodeEncoding(string $encoding) : bool
    {
        return in_array($encoding, self::$encodings, true);
    }

    /**
     * Retrieves a list of all known unicode file encodings.
     * @return string[]
     */
    public function getKnownUnicodeEncodings() : array
    {
        return array_keys(self::$utfBoms);
    }
}
