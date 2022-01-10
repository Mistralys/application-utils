<?php
/**
 * File containing the {@see AppUtils\FileHelper} class.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @see FileHelper
 */

namespace AppUtils;

use DateTime;
use DirectoryIterator;
use ParseCsv\Csv;

/**
 * Collection of file system related methods.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileHelper
{
    public const ERROR_CANNOT_FIND_JSON_FILE = 340001;
    public const ERROR_JSON_FILE_CANNOT_BE_READ = 340002;
    public const ERROR_CANNOT_DECODE_JSON_FILE = 340003;
    public const ERROR_CANNOT_SEND_MISSING_FILE = 340004;
    public const ERROR_JSON_ENCODE_ERROR = 340005;
    public const ERROR_CANNOT_OPEN_URL = 340008;
    public const ERROR_CANNOT_CREATE_FOLDER = 340009;
    public const ERROR_FILE_NOT_READABLE = 340010;
    public const ERROR_CANNOT_COPY_FILE = 340011;
    public const ERROR_CANNOT_DELETE_FILE = 340012;
    public const ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST = 340014;
    public const ERROR_UNKNOWN_FILE_MIME_TYPE = 340015;
    public const ERROR_SERIALIZED_FILE_CANNOT_BE_READ = 340017;
    public const ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED = 340018;
    public const ERROR_UNSUPPORTED_OS_CLI_COMMAND = 340019;
    public const ERROR_SOURCE_FILE_NOT_FOUND = 340020;
    public const ERROR_SOURCE_FILE_NOT_READABLE = 340021;
    public const ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE = 340022;
    public const ERROR_SAVE_FOLDER_NOT_WRITABLE = 340023;
    public const ERROR_SAVE_FILE_NOT_WRITABLE = 340024;
    public const ERROR_SAVE_FILE_WRITE_FAILED = 340025;
    public const ERROR_FILE_DOES_NOT_EXIST = 340026;
    public const ERROR_CANNOT_OPEN_FILE_TO_READ_LINES = 340027;
    public const ERROR_CANNOT_READ_FILE_CONTENTS = 340028;
    public const ERROR_PARSING_CSV = 340029;
    public const ERROR_CURL_INIT_FAILED = 340030;
    public const ERROR_CURL_OUTPUT_NOT_STRING = 340031;
    public const ERROR_CANNOT_OPEN_FILE_TO_DETECT_BOM = 340032;
    public const ERROR_FOLDER_DOES_NOT_EXIST = 340033;
    public const ERROR_PATH_IS_NOT_A_FOLDER = 340034;
    public const ERROR_CANNOT_WRITE_TO_FOLDER = 340035;

    /**
     * @var array<string,string>|NULL
     */
    protected static $utfBoms;

    /**
    * Opens a serialized file and returns the unserialized data.
    * 
    * @param string $file
    * @throws FileHelper_Exception
    * @return array<int|string,mixed>
    * @deprecated Use parseSerializedFile() instead.
    * @see FileHelper::parseSerializedFile()
    */
    public static function openUnserialized(string $file) : array
    {
        return self::parseSerializedFile($file);
    }

   /**
    * Opens a serialized file and returns the unserialized data.
    *
    * @param string $file
    * @throws FileHelper_Exception
    * @return array<int|string,mixed>
    * @see FileHelper::parseSerializedFile()
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    * @see FileHelper::ERROR_SERIALIZED_FILE_CANNOT_BE_READ
    * @see FileHelper::ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED
    */
    public static function parseSerializedFile(string $file) : array
    {
        self::requireFileExists($file);
        
        $contents = file_get_contents($file);
        
        if($contents === false) 
        {
            throw new FileHelper_Exception(
                'Cannot load serialized content from file.',
                sprintf(
                    'Tried reading file contents at [%s].',
                    $file
                ),
                self::ERROR_SERIALIZED_FILE_CANNOT_BE_READ
            );
        }
        
        $result = @unserialize($contents);
        
        if($result !== false) {
            return $result;
        }
        
        throw new FileHelper_Exception(
            'Cannot unserialize the file contents.',
            sprintf(
                'Tried unserializing the data from file at [%s].',
                $file
            ),
            self::ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED
        );
    }

    /**
     * Deletes a folder tree with all files therein, including
     * the specified folder itself.
     *
     * @param string $rootFolder
     * @return bool
     */
    public static function deleteTree(string $rootFolder) : bool
    {
        if(!file_exists($rootFolder)) {
            return true;
        }
        
        $d = new DirectoryIterator($rootFolder);
        foreach ($d as $item) {
            if ($item->isDot()) {
                continue;
            }

            $itemPath = $item->getRealPath();
            if (!is_readable($itemPath)) {
                return false;
            }

            if ($item->isDir()) {
                if (!FileHelper::deleteTree($itemPath)) {
                    return false;
                }
                continue;
            }

            if ($item->isFile()) {
                if (!unlink($itemPath)) {
                    return false;
                }
            }
        }

        return rmdir($rootFolder);
    }
    
   /**
    * Create a folder, if it does not exist yet.
    *  
    * @param string $path
    * @throws FileHelper_Exception
    * @see FileHelper::ERROR_CANNOT_CREATE_FOLDER
    */
    public static function createFolder(string $path) : void
    {
        if(is_dir($path) || mkdir($path, 0777, true)) {
            return;
        }
        
        throw new FileHelper_Exception(
            sprintf('Could not create target folder [%s].', basename($path)),
            sprintf('Tried to create the folder in path [%s].', $path),
            self::ERROR_CANNOT_CREATE_FOLDER
        );
    }

    /**
     * Copies a folder tree to the target folder.
     *
     * @param string $source
     * @param string $target
     * @throws FileHelper_Exception
     */
    public static function copyTree(string $source, string $target) : void
    {
        self::createFolder($target);

        $d = new DirectoryIterator($source);
        foreach ($d as $item) 
        {
            if ($item->isDot()) {
                continue;
            }

            $itemPath = self::requireFileReadable($item->getPathname());
            
            $baseName = basename($itemPath);

            if ($item->isDir()) 
            {
                FileHelper::copyTree($itemPath, $target . '/' . $baseName);
            } 
            else if($item->isFile()) 
            {
                self::copyFile($itemPath, $target . '/' . $baseName);
            }
        }
    }
    
   /**
    * Copies a file to the target location. Includes checks
    * for most error sources, like the source file not being
    * readable. Automatically creates the target folder if it
    * does not exist yet.
    * 
    * @param string $sourcePath
    * @param string $targetPath
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_CANNOT_CREATE_FOLDER
    * @see FileHelper::ERROR_SOURCE_FILE_NOT_FOUND
    * @see FileHelper::ERROR_SOURCE_FILE_NOT_READABLE
    * @see FileHelper::ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE
    * @see FileHelper::ERROR_CANNOT_COPY_FILE
    */
    public static function copyFile(string $sourcePath, string $targetPath) : void
    {
        self::requireFileExists($sourcePath, self::ERROR_SOURCE_FILE_NOT_FOUND);
        self::requireFileReadable($sourcePath, self::ERROR_SOURCE_FILE_NOT_READABLE);
        
        $targetFolder = dirname($targetPath);
        
        if(!file_exists($targetFolder))
        {
            self::createFolder($targetFolder);
        }
        else if(!is_writable($targetFolder)) 
        {
            throw new FileHelper_Exception(
                sprintf('Target folder [%s] is not writable.', basename($targetFolder)),
                sprintf(
                    'Tried copying to target folder [%s].',
                    $targetFolder
                ),
                self::ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE
            );
        }
        
        if(copy($sourcePath, $targetPath)) {
            return;
        }
        
        throw new FileHelper_Exception(
            sprintf('Cannot copy file [%s].', basename($sourcePath)),
            sprintf(
                'The file [%s] could not be copied from [%s] to [%s].',
                basename($sourcePath),
                $sourcePath,
                $targetPath
            ),
            self::ERROR_CANNOT_COPY_FILE
        );
    }
    
   /**
    * Deletes the target file. Ignored if it cannot be found,
    * and throws an exception if it fails.
    * 
    * @param string $filePath
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_CANNOT_DELETE_FILE
    */
    public static function deleteFile(string $filePath) : void
    {
        if(!file_exists($filePath)) {
            return;
        }
        
        if(unlink($filePath)) {
            return;
        }
        
        throw new FileHelper_Exception(
            sprintf('Cannot delete file [%s].', basename($filePath)),
            sprintf(
                'The file [%s] cannot be deleted.',
                $filePath
            ),
            self::ERROR_CANNOT_DELETE_FILE
        );
    }

    /**
    * Creates a new CSV parser instance and returns it.
    * 
    * @param string $delimiter
    * @param string $enclosure
    * @param string $escape
    * @param bool $heading
    * @return Csv
     * @see CSVHelper::createParser()
    */
    public static function createCSVParser(string $delimiter = ';', string $enclosure = '"', string $escape = '\\', bool $heading=false) : Csv
    {
        if($delimiter==='') { $delimiter = ';'; }
        if($enclosure==='') { $enclosure = '"'; }

        $parser = CSVHelper::createParser($delimiter);
        $parser->enclosure = $enclosure;
        $parser->heading = $heading;

        return $parser;
    }

   /**
    * Parses all lines in the specified string and returns an
    * indexed array with all csv values in each line.
    *
    * @param string $csv
    * @param string $delimiter
    * @param string $enclosure
    * @param string $escape
    * @param bool $heading
    * @return array<int,array<string,string>>
    * @throws FileHelper_Exception
    * 
    * @see parseCSVFile()
    * @see FileHelper::ERROR_PARSING_CSV
    */
    public static function parseCSVString(string $csv, string $delimiter = ';', string $enclosure = '"', string $escape = '\\', bool $heading=false) : array
    {
        $parser = self::createCSVParser($delimiter, $enclosure, '\\', $heading);

        if($parser->parse($csv))
        {
            return $parser->data;
        }

        throw new FileHelper_Exception(
            'Could not parse CSV string, possible formatting error.',
            'The parseCSV library returned an error, but exact details are not available.',
            self::ERROR_PARSING_CSV
        );
    }

    /**
     * Parses all lines in the specified file and returns an
     * indexed array with all csv values in each line.
     *
     * @param string $filePath
     * @param string $delimiter 
     * @param string $enclosure The character to use to quote literal strings
     * @param string $escape The character to use to escape special characters.
     * @param bool $heading Whether to include headings.
     * @return array<int,array<string,string>>
     * @throws FileHelper_Exception
     * 
     * @see parseCSVString()
     * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
     * @see FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
     */
    public static function parseCSVFile(string $filePath, string $delimiter = ';', string $enclosure = '"', string $escape = '\\', bool $heading=false) : array
    {
        $content = self::readContents($filePath);

        return self::parseCSVString($content, $delimiter, $enclosure, $escape, $heading);
    }

    /**
     * Detects the mime type for the specified file name/path.
     * Returns null if it is not a known file extension.
     *
     * @param string $fileName
     * @return string|NULL
     */
    public static function detectMimeType(string $fileName) : ?string
    {
        $ext = self::getExtension($fileName);
        if(empty($ext)) {
            return null;
        }

        return FileHelper_MimeTypes::getMime($ext);
    }

    /**
     * Like `sendFile()`, but automatically determines whether
     * the browser can open the target file type, to either
     * send it directly to the browser, or force downloading
     * it instead.
     *
     * @param string $filePath
     * @param string $fileName
     * @throws FileHelper_Exception
     */
    public function sendFileAuto(string $filePath, string $fileName = '') : void
    {
        self::sendFile(
            $filePath,
            $fileName,
            !FileHelper_MimeTypes::canBrowserDisplay(self::getExtension($filePath))
        );
    }

    /**
     * Detects the mime type of the target file automatically,
     * sends the required headers to trigger a download and
     * outputs the file. Returns false if the mime type could
     * not be determined.
     * 
     * @param string $filePath
     * @param string|null $fileName The name of the file for the client.
     * @param bool $asAttachment Whether to force the client to download the file.
     * @throws FileHelper_Exception
     * 
     * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
     * @see FileHelper::ERROR_UNKNOWN_FILE_MIME_TYPE
     */
    public static function sendFile(string $filePath, ?string $fileName = null, bool $asAttachment=true) : void
    {
        self::requireFileExists($filePath);
        
        if(empty($fileName)) {
            $fileName = basename($filePath);
        }

        $mime = self::detectMimeType($filePath);
        if (!$mime) {
            throw new FileHelper_Exception(
                'Unknown file mime type',
                sprintf(
                    'Could not determine mime type for file name [%s].',
                    basename($filePath)
                ),
                self::ERROR_UNKNOWN_FILE_MIME_TYPE
            );
        }
        
        header("Cache-Control: public", true);
        header("Content-Description: File Transfer", true);
        header("Content-Type: " . $mime, true);

        $disposition = 'inline';
        if($asAttachment) {
            $disposition = 'attachment';
        }
        
        header(sprintf(
            "Content-Disposition: %s; filename=%s",
            $disposition,
            '"'.$fileName.'"'
        ), true);
        
        readfile($filePath);
    }

    /**
     * Uses cURL to download the contents of the specified URL,
     * returns the content.
     *
     * @param string $url
     * @throws FileHelper_Exception
     * @return string
     * 
     * @see FileHelper::ERROR_CANNOT_OPEN_URL
     */
    public static function downloadFile(string $url) : string
    {
        $ch = curl_init();
        if(!is_resource($ch)) 
        {
            throw new FileHelper_Exception(
                'Could not initialize a new cURL instance.',
                'Calling curl_init returned false. Additional information is not available.',
                self::ERROR_CURL_INIT_FAILED
            );
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Google Chrome/1.0");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
        
        $output = curl_exec($ch);

        if($output === false) {
            throw new FileHelper_Exception(
                'Unable to open URL',
                sprintf(
                    'Tried accessing URL "%1$s" using cURL, but the request failed. cURL error: %2$s',
                    $url,
                    curl_error($ch)
                ),
                self::ERROR_CANNOT_OPEN_URL
            );
        }

        curl_close($ch);

        if(is_string($output)) 
        {
            return $output;
        }
        
        throw new FileHelper_Exception(
            'Unexpected cURL output.',
            'The cURL output is not a string, although the CURLOPT_RETURNTRANSFER option is set.',
            self::ERROR_CURL_OUTPUT_NOT_STRING
        );
    }
    
   /**
    * Verifies whether the target file is a PHP file. The path
    * to the file can be a path to a file as a string, or a 
    * DirectoryIterator object instance.
    * 
    * @param string|DirectoryIterator $pathOrDirIterator
    * @return boolean
    */
    public static function isPHPFile($pathOrDirIterator) : bool
    {
    	return self::getExtension($pathOrDirIterator) === 'php';
    }
    
   /**
    * Retrieves the extension of the specified file. Can be a path
    * to a file as a string, or a DirectoryIterator object instance.
    * 
    * @param string|DirectoryIterator $pathOrDirIterator
    * @param bool $lowercase
    * @return string
    */
    public static function getExtension($pathOrDirIterator, bool $lowercase = true) : string
    {
        if($pathOrDirIterator instanceof DirectoryIterator) {
            $filename = $pathOrDirIterator->getFilename();
        } else {
            $filename = basename(strval($pathOrDirIterator));
        }
         
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if($lowercase) {
        	$ext = mb_strtolower($ext);
        }
        
        return $ext;
    }
    
   /**
    * Retrieves the file name from a path, with or without extension.
    * The path to the file can be a string, or a DirectoryIterator object
    * instance.
    * 
    * In case of folders, behaves like the pathinfo function: returns
    * the name of the folder.
    * 
    * @param string|DirectoryIterator $pathOrDirIterator
    * @param bool $extension
    * @return string
    */
    public static function getFilename($pathOrDirIterator, $extension = true) : string
    {
        $path = strval($pathOrDirIterator);
    	if($pathOrDirIterator instanceof DirectoryIterator) {
    		$path = $pathOrDirIterator->getFilename();
    	}
    	
    	$path = self::normalizePath($path);
    	
    	if(!$extension) {
    	    return pathinfo($path, PATHINFO_FILENAME);
    	}
    	
    	return pathinfo($path, PATHINFO_BASENAME); 
    }

    /**
     * Tries to read the contents of the target file and
     * treat it as JSON to return the decoded JSON data.
     *
     * @param string $file
     * @param string $targetEncoding
     * @param string|string[]|null $sourceEncoding
     * @return array<int|string,mixed>
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_FIND_JSON_FILE
     * @see FileHelper::ERROR_CANNOT_DECODE_JSON_FILE
     */
    public static function parseJSONFile(string $file, string $targetEncoding='', $sourceEncoding=null) : array
    {
        self::requireFileExists($file, self::ERROR_CANNOT_FIND_JSON_FILE);
        
        $content = self::readContents($file);

        if(!empty($targetEncoding)) {
            $content = mb_convert_encoding($content, $targetEncoding, $sourceEncoding);
        }
        
        $json = json_decode($content, true);
        if($json === false || $json === NULL) {
            throw new FileHelper_Exception(
                'Cannot decode json data',
                sprintf(
                    'Loaded the contents of file [%s] successfully, but decoding it as JSON failed.',
                    $file    
                ),
                self::ERROR_CANNOT_DECODE_JSON_FILE
            );
        }
        
        return $json;
    }
    
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
    public static function fixFileName(string $name) : string
    {
        $name = trim($name);
        $name = self::normalizePath($name);
        $name = basename($name);
        
        $replaces = array(
            "\t" => ' ',
            "\r" => ' ',
            "\n" => ' ',
            ' .' => '.',
            '. ' => '.',
        );
        
        $name = str_replace(array_keys($replaces), array_values($replaces), $name);
        
        while(strstr($name, '  ')) {
            $name = str_replace('  ', ' ', $name);
        }

        $name = str_replace(array_keys($replaces), array_values($replaces), $name);
        
        while(strstr($name, '..')) {
            $name = str_replace('..', '.', $name);
        }
        
        return $name;
    }

    /**
     * Creates an instance of the file finder, which is an easier
     * alternative to the other manual findFile methods, since all
     * options can be set by chaining.
     *
     * @param string $path
     * @return FileHelper_FileFinder
     * @throws FileHelper_Exception
     *
     * @see FileHelper_FileFinder::ERROR_PATH_DOES_NOT_EXIST
     */
    public static function createFileFinder(string $path) : FileHelper_FileFinder
    {
        return new FileHelper_FileFinder($path);
    }

    /**
     * Searches for all HTML files in the target folder.
     *
     * NOTE: This method only exists for backwards compatibility.
     * Use the `createFileFinder()` method instead, which offers
     * an object-oriented interface that is much easier to use.
     *
     * @param string $targetFolder
     * @param array<string,mixed> $options
     * @return string[] An indexed array with files.
     * @throws FileHelper_Exception
     * @see FileHelper::createFileFinder()
     */
    public static function findHTMLFiles(string $targetFolder, array $options=array()) : array
    {
        return self::findFiles($targetFolder, array('html'), $options);
    }

    /**
     * Searches for all PHP files in the target folder.
     *
     * NOTE: This method only exists for backwards compatibility.
     * Use the `createFileFinder()` method instead, which offers
     * an object-oriented interface that is much easier to use.
     *
     * @param string $targetFolder
     * @param array<string,mixed> $options
     * @return string[] An indexed array of PHP files.
     * @throws FileHelper_Exception
     * @see FileHelper::createFileFinder()
     */
    public static function findPHPFiles(string $targetFolder, array $options=array()) : array
    {
        return self::findFiles($targetFolder, array('php'), $options);
    }
    
   /**
    * Finds files according to the specified options.
    * 
    * NOTE: This method only exists for backwards compatibility.
    * Use the `createFileFinder()` method instead, which offers
    * an object oriented interface that is much easier to use.
    *  
    * @param string $targetFolder
    * @param string[] $extensions
    * @param array<string,mixed> $options
    * @throws FileHelper_Exception
    * @return string[]
    * @see FileHelper::createFileFinder()
    */
    public static function findFiles(string $targetFolder, array $extensions=array(), array $options=array()) : array
    {
        $finder = self::createFileFinder($targetFolder);

        foreach ($extensions as $extension) {
            $finder->includeExtension($extension);
        }

        $finder->setPathmodeStrip();
        
        if(isset($options['relative-path']) && $options['relative-path'] === true) 
        {
            $finder->setPathmodeRelative();
        } 
        else if(isset($options['absolute-path']) && $options['absolute-path'] === true)
        {
            $finder->setPathmodeAbsolute();
        }
        
        if(isset($options['strip-extension'])) 
        {
            $finder->stripExtensions();
        }
        
        $finder->setOptions($options);
        
        return $finder->getAll();
    }

   /**
    * Removes the extension from the specified path or file name,
    * if any, and returns the name without the extension.
    * 
    * @param string $filename
    * @param bool $keepPath Whether to keep the path component, if any. Default PHP pathinfo behavior is not to.
    * @return string
    */
    public static function removeExtension(string $filename, bool $keepPath=false) : string
    {
        // normalize paths to allow windows style slashes even on nix servers
        $filename = self::normalizePath($filename);
        
        if(!$keepPath) 
        {
            return pathinfo($filename, PATHINFO_FILENAME);
        }
        
        $parts = explode('/', $filename);
        
        $file = self::removeExtension(array_pop($parts));
        
        $parts[] = $file;
        
        return implode('/', $parts);
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
     * @param string $filename
     * @return string|NULL
     * @throws FileHelper_Exception
     *
     * @see FileHelper::ERROR_CANNOT_OPEN_FILE_TO_DETECT_BOM
     */
    public static function detectUTFBom(string $filename) : ?string
    {
        $fp = fopen($filename, 'r');
        if($fp === false) 
        {
            throw new FileHelper_Exception(
                'Cannot open file for reading',
                sprintf('Tried opening file [%s] in read mode.', $filename),
                self::ERROR_CANNOT_OPEN_FILE_TO_DETECT_BOM
            );
        }
        
        $text = fread($fp, 20);
        
        fclose($fp);

        $boms = self::getUTFBOMs();
        
        foreach($boms as $bom => $value) 
        {
            $length = mb_strlen($value);
            if(mb_substr($text, 0, $length) == $value) {
                return $bom;
            }
        }
        
        return null;
    }

   /**
    * Retrieves a list of all UTF byte order mark character
    * sequences, as an associative array with UTF encoding => bom sequence
    * pairs.
    * 
    * @return array<string,string>
    */
    public static function getUTFBOMs() : array
    {
        if(!isset(self::$utfBoms)) {
            self::$utfBoms = array(
                'UTF32-BE' => chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF),
                'UTF32-LE' => chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00),
                'UTF16-BE' => chr(0xFE) . chr(0xFF),
                'UTF16-LE' => chr(0xFF) . chr(0xFE),
                'UTF8' => chr(0xEF) . chr(0xBB) . chr(0xBF)
            );
        }
        
        return self::$utfBoms;
    }
    
   /**
    * Checks whether the specified encoding is a valid
    * unicode encoding, for example "UTF16-LE" or "UTF8".
    * Also accounts for alternate way to write the, like
    * "UTF-8", and omitting little/big endian suffixes.
    * 
    * @param string $encoding
    * @return boolean
    */
    public static function isValidUnicodeEncoding(string $encoding) : bool
    {
        $encodings = self::getKnownUnicodeEncodings();

        $keep = array();
        foreach($encodings as $string) 
        {
            $withHyphen = str_replace('UTF', 'UTF-', $string);
            
            $keep[] = $string;
            $keep[] = $withHyphen; 
            $keep[] = str_replace(array('-BE', '-LE'), '', $string);
            $keep[] = str_replace(array('-BE', '-LE'), '', $withHyphen);
        }
        
        return in_array($encoding, $keep);
    }
    
   /**
    * Retrieves a list of all known unicode file encodings.
    * @return string[]
    */
    public static function getKnownUnicodeEncodings() : array
    {
        return array_keys(self::getUTFBOMs());
    }
    
   /**
    * Normalizes the slash style in a file or folder path,
    * by replacing any antislashes with forward slashes.
    * 
    * @param string $path
    * @return string
    */
    public static function normalizePath(string $path) : string
    {
        return str_replace(array('\\', '//'), array('/', '/'), $path);
    }
    
   /**
    * Saves the specified data to a file, JSON encoded.
    * 
    * @param mixed $data
    * @param string $file
    * @param bool $pretty
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_JSON_ENCODE_ERROR
    * @see FileHelper::ERROR_SAVE_FOLDER_NOT_WRITABLE
    * @see FileHelper::ERROR_SAVE_FILE_NOT_WRITABLE
    * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
    */
    public static function saveAsJSON($data, string $file, bool $pretty=false) : void
    {
        $options = null;
        if($pretty) {
            $options = JSON_PRETTY_PRINT;
        }
        
        $json = json_encode($data, $options);
        
        if($json===false) 
        {
            $errorCode = json_last_error();
            
            throw new FileHelper_Exception(
                'An error occurred while encdoding a data set to JSON. Native error message: ['.json_last_error_msg().'].', 
                'JSON error code: '.$errorCode,
                self::ERROR_JSON_ENCODE_ERROR
            ); 
        }
        
        self::saveFile($file, $json);
    }
   
   /**
    * Saves the specified content to the target file, creating
    * the file and the folder as necessary.
    * 
    * @param string $filePath
    * @param string $content
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_SAVE_FOLDER_NOT_WRITABLE
    * @see FileHelper::ERROR_SAVE_FILE_NOT_WRITABLE
    * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
    */
    public static function saveFile(string $filePath, string $content='') : void
    {
        $filePath = self::normalizePath($filePath);
        
        // target file already exists
        if(file_exists($filePath))
        {
            if(!is_writable($filePath))
            {
                throw new FileHelper_Exception(
                    sprintf('Cannot save file: target file [%s] exists, but is not writable.', basename($filePath)),
                    sprintf(
                        'Tried accessing the file in path [%s].',
                        $filePath
                    ),
                    self::ERROR_SAVE_FILE_NOT_WRITABLE
                );
            }
        }
        // target file does not exist yet
        else
        {
            $targetFolder = dirname($filePath);
            
            // create the folder as needed
            self::createFolder($targetFolder);
            
            if(!is_writable($targetFolder)) 
            {
                throw new FileHelper_Exception(
                    sprintf('Cannot save file: target folder [%s] is not writable.', basename($targetFolder)),
                    sprintf(
                        'Tried accessing the folder in path [%s].',
                        $targetFolder
                    ),
                    self::ERROR_SAVE_FOLDER_NOT_WRITABLE
                );
            }
        }
        
        if(is_dir($filePath))
        {
            throw new FileHelper_Exception(
                sprintf('Cannot save file: the target [%s] is a directory.', basename($filePath)),
                sprintf(
                    'Tried saving content to path [%s].',
                    $filePath
                ),
                self::ERROR_CANNOT_WRITE_TO_FOLDER
            );
        }
        
        if(file_put_contents($filePath, $content) !== false) {
            return;
        }
        
        throw new FileHelper_Exception(
            sprintf('Cannot save file: writing content to the file [%s] failed.', basename($filePath)),
            sprintf(
                'Tried saving content to file in path [%s].',
                $filePath
            ),
            self::ERROR_SAVE_FILE_WRITE_FAILED
        );
    }

    /**
     * Checks whether it is possible to run PHP command
     * line commands.
     *
     * @return boolean
     * @throws FileHelper_Exception
     */
    public static function canMakePHPCalls() : bool
    {
        return self::cliCommandExists('php');
    }
    
    /**
     * Determines if a command exists on the current environment's command line interface.
     *
     * @param string $command The name of the command to check, e.g. "php"
     * @return bool True if the command has been found, false otherwise.
     * @throws FileHelper_Exception 
     * 
     * @todo Move this to a separate class.
     */
    public static  function cliCommandExists(string $command) : bool
    {
        static $checked = array();
        
        if(isset($checked[$command])) {
            return $checked[$command];
        }
        
        // command to use to search for available commands
        // on the target OS
        $osCommands = array(
            'windows' => 'where',
            'linux' => 'which'
        );
        
        $os = strtolower(PHP_OS_FAMILY);
        
        if(!isset($osCommands[$os])) 
        {
            throw new FileHelper_Exception(
                'Unsupported OS for CLI commands',
                sprintf(
                    'The command to search for available CLI commands is not known for the OS [%s].',
                    $os
                ),
                self::ERROR_UNSUPPORTED_OS_CLI_COMMAND
            );
        }
        
        $whereCommand = $osCommands[$os];
        
        $pipes = array();
        
        $process = proc_open(
            $whereCommand.' '.$command,
            array(
                0 => array("pipe", "r"), //STDIN
                1 => array("pipe", "w"), //STDOUT
                2 => array("pipe", "w"), //STDERR
            ),
            $pipes
        );
        
        if($process === false) {
            $checked[$command] = false;
            return false;
        }
        
        $stdout = stream_get_contents($pipes[1]);
        
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($process);
        
        $result = $stdout != '';
        
        $checked[$command] = $result;
        
        return $result;
    }

    /**
     * Validates a PHP file's syntax.
     *
     * NOTE: This will fail silently if the PHP command line
     * is not available. Use {@link FileHelper::canMakePHPCalls()}
     * to check this beforehand as needed.
     *
     * @param string $path
     * @return boolean|string[] A boolean true if the file is valid, an array with validation messages otherwise.
     * @throws FileHelper_Exception
     */
    public static function checkPHPFileSyntax(string $path)
    {
        if(!self::canMakePHPCalls()) {
            return true;
        }
        
        $output = array();
        $command = sprintf('php -l "%s" 2>&1', $path);
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
    
   /**
    * Retrieves the last modified date for the specified file or folder.
    * 
    * Note: If the target does not exist, returns null. 
    * 
    * @param string $path
    * @return DateTime|NULL
    */
    public static function getModifiedDate(string $path) : ?DateTime
    {
        $time = filemtime($path);
        if($time === false) {
            return null;
        }

        $date = new DateTime();
        $date->setTimestamp($time);
        return $date;
    }
    
   /**
    * Retrieves the names of all sub-folders in the specified path.
    * 
    * Available options:
    * 
    * - recursive: true/false
    *   Whether to search for sub-folders recursively.
    *   
    * - absolute-paths: true/false
    *   Whether to return a list of absolute paths.
    * 
    * @param string|DirectoryIterator $targetFolder
    * @param array<string,mixed> $options
    * @throws FileHelper_Exception
    * @return string[]
    *
    * @see FileHelper::ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST
    * @todo Move this to a separate class.
    */
    public static function getSubfolders($targetFolder, array $options = array())
    {
        if($targetFolder instanceof DirectoryIterator) {
            $targetFolder = $targetFolder->getPathname();
        }

        if(!is_dir($targetFolder)) 
        {
            throw new FileHelper_Exception(
                'Target folder does not exist',
                sprintf(
                    'Cannot retrieve sub-folders from [%s], the folder does not exist.',
                    $targetFolder
                ),
                self::ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST
            );
        }
        
        $options = array_merge(
            array(
                'recursive' => false,
                'absolute-path' => false
            ), 
            $options
        );
        
        $result = array();
        
        $d = new DirectoryIterator($targetFolder);
        
        foreach($d as $item) 
        {
            if($item->isDir() && !$item->isDot()) 
            {
                $name = $item->getFilename();
                
                if(!$options['absolute-path']) {
                    $result[] = $name;
                } else {
                    $result[] = $targetFolder.'/'.$name;
                }
                
                if(!$options['recursive']) 
                {
                    continue;
                }
                
                $subs = self::getSubfolders($targetFolder.'/'.$name, $options);
                foreach($subs as $sub) 
                {
                    $relative = $name.'/'.$sub;
                    
                    if(!$options['absolute-path']) {
                        $result[] = $relative;
                    } else {
                        $result[] = $targetFolder.'/'.$relative;
                    }
                }
            }
        }
        
        return $result;
    }

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
    public static function getMaxUploadFilesize() : int
    {
        static $max_size = -1;
        
        if ($max_size < 0)
        {
            // Start with post_max_size.
            $post_max_size = self::parse_size(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }
            
            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        
        return $max_size;
    }
    
    protected static function parse_size(string $size) : float
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = floatval(preg_replace('/[^0-9\.]/', '', $size)); // Remove the non-numeric characters from the size.
        
        if($unit) 
        {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }
   
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
    public static function relativizePathByDepth(string $path, int $depth=2) : string
    {
        $path = self::normalizePath($path);
        
        $tokens = explode('/', $path);
        $tokens = array_filter($tokens); // remove empty entries (trailing slash for example)
        $tokens = array_values($tokens); // re-index keys
        
        if(empty($tokens)) {
            return '';
        }
        
        // remove the drive if present
        if(strstr($tokens[0], ':')) {
            array_shift($tokens);
        }
        
        // path was only the drive
        if(count($tokens) == 0) {
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
    public static function relativizePath(string $path, string $relativeTo) : string
    {
        $path = self::normalizePath($path);
        $relativeTo = self::normalizePath($relativeTo);
        
        $relative = str_replace($relativeTo, '', $path);
        $relative = trim($relative, '/');
        
        return $relative;
    }
    
   /**
    * Checks that the target file exists, and throws an exception
    * if it does not. 
    * 
    * @param string $path
    * @param int|NULL $errorCode Optional custom error code
    * @throws FileHelper_Exception
    * @return string The real path to the file
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    */
    public static function requireFileExists(string $path, ?int $errorCode=null) : string
    {
        $result = realpath($path);
        if($result !== false) {
            return $result;
        }
        
        if($errorCode === null) {
            $errorCode = self::ERROR_FILE_DOES_NOT_EXIST;
        }
        
        throw new FileHelper_Exception(
            sprintf('File [%s] does not exist.', basename($path)),
            sprintf('Tried finding the file in path [%s].', $path),
            $errorCode
        );
    }

    /**
     * @param string $path
     * @param int|NULL $errorCode
     * @return string
     * @throws FileHelper_Exception
     */
    public static function requireFileReadable(string $path, ?int $errorCode=null) : string
    {
        $path = self::requireFileExists($path, $errorCode);

        if(is_readable($path)) {
            return $path;
        }

        if($errorCode === null) {
            $errorCode = self::ERROR_FILE_NOT_READABLE;
        }

        throw new FileHelper_Exception(
            sprintf('File [%s] is not readable.', basename($path)),
            sprintf('Tried accessing the file in path [%s].', $path),
            $errorCode
        );
    }
    
   /**
    * Reads a specific line number from the target file and returns its
    * contents, if the file has such a line. Does so with little memory
    * usage, as the file is not read entirely into memory.
    * 
    * @param string $path
    * @param int $lineNumber Note: 1-based; the first line is number 1.
    * @return string|NULL Will return null if the requested line does not exist.
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    */
    public static function getLineFromFile(string $path, int $lineNumber) : ?string
    {
        self::requireFileExists($path);
        
        $file = new \SplFileObject($path);
        
        if($file->eof()) {
            return '';
        }
        
        $targetLine = $lineNumber-1;
        
        $file->seek($targetLine);
        
        if($file->key() !== $targetLine) {
             return null;
        }
        
        return $file->current(); 
    }
    
   /**
    * Retrieves the total amount of lines in the file, without 
    * reading the whole file into memory.
    * 
    * @param string $path
    * @return int
    */
    public static function countFileLines(string $path) : int
    {
        self::requireFileExists($path);
        
        $spl = new \SplFileObject($path);
        
        // tries seeking as far as possible
        $spl->seek(PHP_INT_MAX);
        
        $number = $spl->key();
        
        // if seeking to the end the cursor is still at 0, there are no lines. 
        if($number === 0) 
        {
            // since it's a very small file, to get reliable results,
            // we read its contents and use that to determine what
            // kind of contents we are dealing with. Tests have shown 
            // that this is not pactical to solve with the SplFileObject.
            $content = file_get_contents($path);
            
            if(empty($content)) {
                return 0;
            }
        }
        
        // return the line number we were able to reach + 1 (key is zero-based)
        return $number+1;
    }
    
   /**
    * Parses the target file to detect any PHP classes contained
    * within, and retrieve information on them. Does not use the 
    * PHP reflection API.
    * 
    * @param string $filePath
    * @return FileHelper_PHPClassInfo
    */
    public static function findPHPClasses(string $filePath) : FileHelper_PHPClassInfo
    {
        return new FileHelper_PHPClassInfo($filePath);
    }
    
   /**
    * Detects the end of line style used in the target file, if any.
    * Can be used with large files, because it only reads part of it.
    * 
    * @param string $filePath The path to the file.
    * @return NULL|ConvertHelper_EOL The end of line character information, or NULL if none is found.
    */
    public static function detectEOLCharacter(string $filePath) : ?ConvertHelper_EOL
    {
        // 20 lines is enough to get a good picture of the newline style in the file.
        $amount = 20;
        
        $lines = self::readLines($filePath, $amount);
        
        $string = implode('', $lines);
        
        return ConvertHelper::detectEOLCharacter($string);
    }

    /**
     * Reads the specified amount of lines from the target file.
     * Unicode BOM compatible: any byte order marker is stripped
     * from the resulting lines.
     *
     * @param string $filePath
     * @param int $amount Set to 0 to read all lines.
     * @return string[]
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
     * @see FileHelper::ERROR_CANNOT_OPEN_FILE_TO_READ_LINES
     */
    public static function readLines(string $filePath, int $amount=0) : array
    {
        self::requireFileExists($filePath);
        
        $fn = fopen($filePath, "r");
        
        if($fn === false) 
        {
            throw new FileHelper_Exception(
                'Could not open file for reading.',
                sprintf(
                    'Tried accessing file at [%s].',
                    $filePath
                ),
                self::ERROR_CANNOT_OPEN_FILE_TO_READ_LINES
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
            
            if($amount > 0 && $counter == $amount) {
                break;
            }
        }
        
        fclose($fn);
        
        return $result;
    }
    
   /**
    * Reads all content from a file.
    * 
    * @param string $filePath
    * @throws FileHelper_Exception
    * @return string
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    * @see FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
    */
    public static function readContents(string $filePath) : string
    {
        self::requireFileExists($filePath);
        
        $result = file_get_contents($filePath);
        
        if($result !== false) {
            return $result;
        }
        
        throw new FileHelper_Exception(
            sprintf('Cannot read contents of file [%s].', basename($filePath)),
            sprintf(
                'Tried opening file for reading at: [%s].',
                $filePath
            ),
            self::ERROR_CANNOT_READ_FILE_CONTENTS
        );
    }

   /**
    * Ensures that the target path exists on disk, and is a folder.
    * 
    * @param string $path
    * @return string The real path, with normalized slashes.
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::normalizePath()
    * 
    * @see FileHelper::ERROR_FOLDER_DOES_NOT_EXIST
    * @see FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
    */
    public static function requireFolderExists(string $path) : string
    {
        $actual = realpath($path);
        
        if($actual === false) 
        {
            throw new FileHelper_Exception(
                'Folder does not exist',
                sprintf(
                    'The path [%s] does not exist on disk.',
                    $path
                ),
                self::ERROR_FOLDER_DOES_NOT_EXIST
            );
        }
        
        if(is_dir($path)) 
        {
            return self::normalizePath($actual);
        }
        
        throw new FileHelper_Exception(
            'Target is not a folder',
            sprintf(
                'The path [%s] does not point to a folder.',
                $path
            ),
            self::ERROR_PATH_IS_NOT_A_FOLDER
        );
    }

    /**
     * Creates an instance of the paths reducer tool, which can reduce
     * a list of paths to the closest common root folder.
     *
     * @param string[] $paths
     * @return FileHelper_PathsReducer
     */
    public static function createPathsReducer(array $paths=array()) : FileHelper_PathsReducer
    {
        return new FileHelper_PathsReducer();
    }
}
