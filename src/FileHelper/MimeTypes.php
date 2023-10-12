<?php
/**
 * File containing the class {@see FileHelper_MimeTypes}.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @see FileHelper_MimeTypes
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\FileHelper\MimeTypesEnum;

/**
 * Collection of file mime types by extension.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileHelper_MimeTypes
{

    /**
     * List of extensions that can typically be opened in a
     * browser natively, without needing separate software.
     *
     * @var string[]
     */
    protected static array $browserExtensions = self::DEFAULT_BROWSER_EXTENSIONS;

    public const DEFAULT_BROWSER_EXTENSIONS = array(
        // Text based files
        'html',
        'htm',
        'php',
        'txt',
        'css',
        'xml',
        'js',
        'json',
        'sql',

        // Documents
        'pdf',
        'csv',

        // Images
        'jpeg',
        'jpg',
        'png',
        'gif',
        'tif',
        'tiff',
        'bmp',
        'ico',
        'svg',
        'tga',

        // Video
        'mpg',
        'mpeg',
        'avi',
        'webm',
        'mov',
        'qt',
        'mp4',
        'divx',
        'mkv',

        // Audio
        'mp3',
        'wav',
        'wma',
        'm4a'
    );

    /**
     * Table with file extension => mime type mappings
     * @var array<string|int,string>
     */
    protected static array $mimeTypes = self::DEFAULT_MIME_TYPES;

    public const DEFAULT_MIME_TYPES = array(
        '323' => 'text/h323',
        'acx' => 'application/internet-property-stream',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'asf' => 'video/x-ms-asf',
        'asr' => 'video/x-ms-asf',
        'asx' => 'video/x-ms-asf',
        'au' => MimeTypesEnum::MIME_AUDIO_BASIC,
        'avi' => MimeTypesEnum::MIME_VIDEO_AVI,
        'axs' => 'application/olescript',
        'bas' => MimeTypesEnum::MIME_TEXT_PLAIN,
        'bcpio' => 'application/x-bcpio',
        'bin' => MimeTypesEnum::MIME_APPLICATION_OCTET_STREAM,
        'bmp' => MimeTypesEnum::MIME_IMAGE_BMP,
        'c' => MimeTypesEnum::MIME_TEXT_PLAIN,
        'cat' => 'application/vnd.ms-pkiseccat',
        'cdf' => 'application/x-cdf',
        'cer' => 'application/x-x509-ca-cert',
        'class' => MimeTypesEnum::MIME_APPLICATION_OCTET_STREAM,
        'clp' => 'application/x-msclip',
        'cmx' => 'image/x-cmx',
        'cod' => 'mage/cis-cod',
        'cpio' => 'application/x-cpio',
        'crd' => 'application/x-mscardfile',
        'crl' => 'application/pkix-crl',
        'crt' => 'application/x-x509-ca-cert',
        'csh' => 'application/x-csh',
        'css' => MimeTypesEnum::MIME_TEXT_CSS,
        'csv' => MimeTypesEnum::MIME_TEXT_CSV,
        'dcr' => 'application/x-director',
        'dds' => 'image/vnd-ms.dds',
        'der' => 'application/x-x509-ca-cert',
        'dir' => 'application/x-director',
        'dll' => 'application/x-msdownload',
        'dms' => MimeTypesEnum::MIME_APPLICATION_OCTET_STREAM,
        'doc' => MimeTypesEnum::MIME_APPLICATION_MSWORD,
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dot' => MimeTypesEnum::MIME_APPLICATION_MSWORD,
        'dvi' => 'application/x-dvi',
        'dxr' => 'application/x-director',
        'eps' => 'application/postscript',
        'etx' => 'text/x-setext',
        'evy' => 'application/envoy',
        'exe' => MimeTypesEnum::MIME_APPLICATION_OCTET_STREAM,
        'fif' => 'application/fractals',
        'flr' => 'x-world/x-vrml',
        'gif' => MimeTypesEnum::MIME_IMAGE_GIF,
        'gtar' => MimeTypesEnum::MIME_ARCHIVE_GTAR,
        'gz' => MimeTypesEnum::MIME_ARCHIVE_GZIP,
        'h' => MimeTypesEnum::MIME_TEXT_PLAIN,
        'hdf' => 'application/x-hdf',
        'hlp' => 'application/winhlp',
        'hqx' => 'application/mac-binhex40',
        'hta' => 'application/hta',
        'htc' => MimeTypesEnum::MIME_TEXT_COMPONENT,
        'htm' => MimeTypesEnum::MIME_TEXT_HTML,
        'html' => MimeTypesEnum::MIME_TEXT_HTML,
        'htt' => 'text/webviewhtml',
        'ico' => MimeTypesEnum::MIME_IMAGE_ICON,
        'ief' => MimeTypesEnum::MIME_IMAGE_IEF,
        'iii' => 'application/x-iphone',
        'ins' => 'application/x-internet-signup',
        'isp' => 'application/x-internet-signup',
        'jfif' => 'image/pipeg',
        'jpe' => MimeTypesEnum::MIME_IMAGE_JPEG,
        'jpeg' => MimeTypesEnum::MIME_IMAGE_JPEG,
        'jpg' => MimeTypesEnum::MIME_IMAGE_JPEG,
        'js' => MimeTypesEnum::MIME_TEXT_JAVASCRIPT,
        'json' => MimeTypesEnum::MIME_TEXT_JSON,
        'latex' => 'application/x-latex',
        'lha' => MimeTypesEnum::MIME_APPLICATION_OCTET_STREAM,
        'lsf' => MimeTypesEnum::MIME_VIDEO_ASF,
        'lsx' => MimeTypesEnum::MIME_VIDEO_ASF,
        'lzh' => MimeTypesEnum::MIME_APPLICATION_OCTET_STREAM,
        'm13' => 'application/x-msmediaview',
        'm14' => 'application/x-msmediaview',
        'm3u' => MimeTypesEnum::MIME_AUDIO_MPEGURL,
        'man' => 'application/x-troff-man',
        'mdb' => 'application/x-msaccess',
        'me' => 'application/x-troff-me',
        'mht' => MimeTypesEnum::MIME_TEXT_RFC822,
        'mhtml' => MimeTypesEnum::MIME_TEXT_RFC822,
        'mid' => MimeTypesEnum::MIME_AUDIO_MID,
        'mny' => 'application/x-msmoney',
        'mov' => MimeTypesEnum::MIME_VIDEO_QUICKTIME,
        'movie' => 'video/x-sgi-movie',
        'mp2' => MimeTypesEnum::MIME_VIDEO_MPEG,
        'mp3' => MimeTypesEnum::MIME_AUDIO_MPEG,
        'mp4' => MimeTypesEnum::MIME_VIDEO_MP4,
        'mpa' => MimeTypesEnum::MIME_VIDEO_MPEG,
        'mpe' => MimeTypesEnum::MIME_VIDEO_MPEG,
        'mpeg' => MimeTypesEnum::MIME_VIDEO_MPEG,
        'mpg' => MimeTypesEnum::MIME_VIDEO_MPEG,
        'mpp' => 'application/vnd.ms-project',
        'mpv2' => MimeTypesEnum::MIME_VIDEO_MPEG,
        'ms' => 'application/x-troff-ms',
        'msg' => MimeTypesEnum::MIME_APPLICATION_OUTLOOK,
        'mvb' => 'application/x-msmediaview',
        'nc' => 'application/x-netcdf',
        'nws' => MimeTypesEnum::MIME_TEXT_RFC822,
        'oda' => 'application/oda',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'p10' => 'application/pkcs10',
        'p12' => 'application/x-pkcs12',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7c' => 'application/x-pkcs7-mime',
        'p7m' => 'application/x-pkcs7-mime',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'p7s' => 'application/x-pkcs7-signature',
        'pbm' => MimeTypesEnum::MIME_IMAGE_PBM,
        'pdf' => MimeTypesEnum::MIME_APPLICATION_PDF,
        'pfx' => 'application/x-pkcs12',
        'pgm' => 'image/x-portable-graymap',
        'pko' => 'application/ynd.ms-pkipko',
        'pma' => 'application/x-perfmon',
        'pmc' => 'application/x-perfmon',
        'pml' => 'application/x-perfmon',
        'pmr' => 'application/x-perfmon',
        'pmw' => 'application/x-perfmon',
        'png' => MimeTypesEnum::MIME_IMAGE_PNG,
        'pnm' => 'image/x-portable-anymap',
        'pot' => 'application/vnd.ms-powerpoint',
        'ppm' => 'image/x-portable-pixmap',
        'pps' => 'application/vnd.ms-powerpoint',
        'ppt' => 'application/vnd.ms-powerpoint',
        'prf' => 'application/pics-rules',
        'ps' => 'application/postscript',
        'pub' => 'application/x-mspublisher',
        'qt' => MimeTypesEnum::MIME_VIDEO_QUICKTIME,
        'ra' => 'audio/x-pn-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'ras' => 'image/x-cmu-raster',
        'rgb' => 'image/x-rgb',
        'rmi' => MimeTypesEnum::MIME_AUDIO_MID,
        'roff' => 'application/x-troff',
        'rtf' => MimeTypesEnum::MIME_APPLICATION_RTF,
        'rtx' => MimeTypesEnum::MIME_TEXT_RICHTEXT,
        'sql' => MimeTypesEnum::MIME_APPLICATION_SQL,
        'scd' => 'application/x-msschedule',
        'sct' => 'text/scriptlet',
        'ser' => MimeTypesEnum::MIME_TEXT_PLAIN,
        'bak' => MimeTypesEnum::MIME_ARCHIVE_ZIP,
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'sit' => 'application/x-stuffit',
        'snd' => MimeTypesEnum::MIME_AUDIO_BASIC,
        'spc' => 'application/x-pkcs7-certificates',
        'spl' => 'application/futuresplash',
        'src' => 'application/x-wais-source',
        'sst' => 'application/vnd.ms-pkicertstore',
        'stl' => 'application/vnd.ms-pkistl',
        'stm' => MimeTypesEnum::MIME_TEXT_HTML,
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svg' => MimeTypesEnum::MIME_IMAGE_SVG,
        'swf' => 'application/x-shockwave-flash',
        't' => 'application/x-troff',
        'tar' => MimeTypesEnum::MIME_ARCHIVE_TAR,
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tgz' => MimeTypesEnum::MIME_ARCHIVE_TGZ,
        'tif' => MimeTypesEnum::MIME_IMAGE_TIFF,
        'tiff' => MimeTypesEnum::MIME_IMAGE_TIFF,
        'ttf' => MimeTypesEnum::MIME_FONT_TTF,
        'tr' => 'application/x-troff',
        'trm' => 'application/x-msterminal',
        'tsv' => 'text/tab-separated-values',
        'txt' => MimeTypesEnum::MIME_TEXT_PLAIN,
        'uls' => 'text/iuls',
        'ustar' => 'application/x-ustar',
        'vcf' => 'text/x-vcard',
        'vrml' => 'x-world/x-vrml',
        'wav' => MimeTypesEnum::MIME_AUDIO_WAV,
        'webm' => MimeTypesEnum::MIME_VIDEO_WEBM,
        'wcm' => 'application/vnd.ms-works',
        'wdb' => 'application/vnd.ms-works',
        'wks' => 'application/vnd.ms-works',
        'wmf' => 'application/x-msmetafile',
        'wps' => 'application/vnd.ms-works',
        'wri' => 'application/x-mswrite',
        'wrl' => 'x-world/x-vrml',
        'wrz' => 'x-world/x-vrml',
        'xaf' => 'x-world/x-vrml',
        'xbm' => 'image/x-xbitmap',
        'xla' => MimeTypesEnum::MIME_APPLICATION_EXCEL,
        'xlc' => MimeTypesEnum::MIME_APPLICATION_EXCEL,
        'xlm' => MimeTypesEnum::MIME_APPLICATION_EXCEL,
        'xls' => MimeTypesEnum::MIME_APPLICATION_EXCEL,
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlt' => MimeTypesEnum::MIME_APPLICATION_EXCEL,
        'xlw' => MimeTypesEnum::MIME_APPLICATION_EXCEL,
        'xof' => 'x-world/x-vrml',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'xml' => MimeTypesEnum::MIME_TEXT_XML,
        'z' => MimeTypesEnum::MIME_ARCHIVE_Z,
        '7z' => MimeTypesEnum::MIME_ARCHIVE_7Z,
        'zip' => MimeTypesEnum::MIME_ARCHIVE_ZIP
    );

    /**
     * Resets all mime types and browser extensions to
     * the default values.
     *
     * @return void
     */
    public static function resetToDefaults() : void
    {
        self::$mimeTypes = self::DEFAULT_MIME_TYPES;
        self::$browserExtensions = self::DEFAULT_BROWSER_EXTENSIONS;
    }

    /**
     * Retrieves the mime type for the specified extension.
     *
     * @param string $extension Case-insensitive extension, with or without a leading dot.
     * @return string|NULL
     */
    public static function getMime(string $extension) : ?string
    {
        $extension = self::filterExtension($extension);

        if (isset(self::$mimeTypes[$extension]))
        {
            return self::$mimeTypes[$extension];
        }

        return null;
    }

    private static function filterExtension(string $extension) : string
    {
        return ltrim(strtolower($extension), '.');
    }

    /**
     * Checks whether a browser can typically display files
     * natively that have the specified extension.
     *
     * @param string $extension
     * @return bool
     */
    public static function canBrowserDisplay(string $extension) : bool
    {
        return in_array(self::filterExtension($extension), self::$browserExtensions);
    }

    /**
     * Sets whether the browser can display files with the
     * specified extension natively.
     *
     * @param string $extension
     * @param bool $canDisplay
     * @return void
     */
    public static function setBrowserCanDisplay(string $extension, bool $canDisplay) : void
    {
        $extension = self::filterExtension($extension);

        if($canDisplay && !in_array($extension, self::$browserExtensions))
        {
            self::$browserExtensions[] = $extension;
        }

        if(!$canDisplay && in_array($extension, self::$browserExtensions))
        {
            $key = array_search($extension, self::$browserExtensions);
            unset(self::$browserExtensions[$key]);
        }
    }

    /**
     * Register custom extensions and related mimetypes.
     *
     * @param string $extension
     * @param string $mimeType
     * @return string|NULL The old mime type, or NULL if none was set.
     * @deprecated Use {@see setMimeType()} instead.
     */
    public static function registerCustom(string $extension, string $mimeType) : ?string
    {
        return self::setMimeType($extension, $mimeType);
    }

    /**
     * Add a mime type, or overwrite the type of existing extensions.
     *
     * @param string $extension
     * @param string $mimeType
     * @return string|NULL The old mime type, or NULL if none was set.
     */
    public static function setMimeType(string $extension, string $mimeType) : ?string
    {
        $extension = self::filterExtension($extension);

        $old = self::$mimeTypes[$extension] ?? null;

        self::$mimeTypes[$extension] = strtolower($mimeType);

        return $old;
    }

    public static function extensionExists(string $extension) : bool
    {
        return isset(self::$mimeTypes[self::filterExtension($extension)]);
    }

    /**
     * Attempts to find the extensions for the specified mime type.
     *
     * Note: Returns a list of extensions, as mime types can have
     * multiple extensions (like {@see FileHelper\MimeTypesEnum::MIME_APPLICATION_EXCEL}).
     *
     * @param string $mime E.g. "image/jpeg"
     * @return string[] List of extensions matching the mime type, or an empty array if none were found.
     */
    public static function getExtensionsByMime(string $mime) : array
    {
        $mime = strtolower($mime);
        $result = array();

        foreach(self::$mimeTypes as $extension => $mimeType)
        {
            if($mime === $mimeType)
            {
                $result[] = $extension;
            }
        }

        sort($result);

        return $result;
    }
}
