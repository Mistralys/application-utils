<?php

class FileHelper
{
    const ERROR_CANNOT_FIND_JSON_FILE = 340001;
    
    const ERROR_JSON_FILE_CANNOT_BE_READ = 340002;
    
    const ERROR_CANNOT_DECODE_JSON_FILE = 340003;
    
    const ERROR_CANNOT_SEND_MISSING_FILE = 340004;
    
    const ERROR_JSON_ENCODE_ERROR = 340005;
    
    const ERROR_JSON_CANNOT_WRITE_FILE = 340006;
    
    const ERROR_CURL_EXTENSION_NOT_INSTALLED = 340007;
    
    const ERROR_CANNOT_OPEN_URL = 340008;
    
    const ERROR_CANNOT_CREATE_FOLDER = 340009;
    
    const ERROR_FILE_NOT_READABLE = 340010;
    
    const ERROR_CANNOT_COPY_FILE = 340011;
    
    const ERROR_CANNOT_DELETE_FILE = 340012;
    
    const ERROR_FIND_FOLDER_DOES_NOT_EXIST = 340013;
     
    const ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST = 340014;
    
    const ERROR_UNKNOWN_FILE_MIME_TYPE = 340015;
    
    public static function openUnserialized($file)
    {
        $contents = file_get_contents($file);
        if (!$contents) {
            return false;
        }

        return unserialize($contents);
    }

    public static function deleteTree($rootFolder)
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
    
    public static function createFolder($path)
    {
        if(is_dir($path) || mkdir($path, 0777, true)) {
            return;
        }
        
        throw new FileHelper_Exception(
            'Could not create target folder.',
            sprintf('Tried to create the folder in path [%s].', $path),
            self::ERROR_CANNOT_CREATE_FOLDER
        );
    }

    public static function copyTree($source, $target)
    {
        self::createFolder($target);

        $d = new DirectoryIterator($source);
        foreach ($d as $item) 
        {
            if ($item->isDot()) {
                continue;
            }

            $itemPath = $item->getRealPath();
            if (!is_readable($itemPath)) {
                throw new FileHelper_Exception(
                    'Source file is not readable',
                    sprintf('The file [%s] cannot be accessed for reading.', $itemPath),
                    self::ERROR_FILE_NOT_READABLE
                );
            }
            
            $baseName = basename($itemPath);

            if ($item->isDir()) 
            {
                FileHelper::copyTree(str_replace('\\', '/', $itemPath), $target . '/' . $baseName);
            } 
            else if($item->isFile()) 
            {
                self::copyFile($itemPath, $target . '/' . $baseName);
            }
        }
    }
    
   /**
    * Copies a file to the target location.
    * 
    * @param string $sourcePath
    * @param string $targetPath
    * @throws FileHelper_Exception
    */
    public static function copyFile($sourcePath, $targetPath)
    {
        if (copy($sourcePath, $targetPath)) {
            return;
        }
        
        throw new FileHelper_Exception(
            'Cannot copy file',
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
    * Deletes the target file. Igored if it cannot be found.
    * 
    * @param string $filePath
    * @throws FileHelper_Exception
    */
    public static function deleteFile($filePath)
    {
        if(!file_exists($filePath)) {
            return;
        }
        
        if(!unlink($filePath)) {
            throw new FileHelper_Exception(
                'Cannot delete file',
                sprintf(
                    'The file [%s] cannot be deleted.',
                    $filePath
                ),
                self::ERROR_CANNOT_DELETE_FILE
            );
        }
    }

    /**
    * Creates a new CSV parser instance and returns it.
    * @param string $delimiter
    * @param string $enclosure
    * @param string $escape
    * @param string $heading
    * @return parseCSV
    */
    public static function createCSVParser($delimiter = ';', $enclosure = '"', $escape = '\\', $heading=false)
    {
        if($delimiter===null) { $delimiter = ';'; }
        if($enclosure===null) { $enclosure = '"'; }
        if($escape===null) { $escape = '\\'; }
        
        $parser = new parseCSV(null, null, null, array());

        $parser->delimiter = $delimiter;
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
     * @return array
     * @see parseCSVFile()
     */
    public static function parseCSVString($csv, $delimiter = ';', $enclosure = '"', $escape = '\\', $heading=false)
    {
        $parser = self::createCSVParser($delimiter, $enclosure, $escape, $heading);
        return $parser->parse_string($csv);
    }

    /**
     * Parses all lines in the specified file and returns an
     * indexed array with all csv values in each line.
     *
     * @param string $csv
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return array
     * @see parseCSVString()
     */
    public static function parseCSVFile($filePath, $delimiter = ';', $enclosure = '"', $escape = '\\', $heading=false)
    {
        $content = file_get_contents($filePath);
        if (!$content) {
            return false;
        }

        return self::parseCSVString($content, $delimiter, $enclosure, $escape, $heading);
    }

    /**
     * Detects the mime type for the specified file name/path.
     * Returns null if it is not a known file extension.
     *
     * @param string $fileName
     * @return string|NULL
     */
    public static function detectMimeType($fileName)
    {
        require_once 'FileHelper/MimeTypes.php';
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return FileHelper_MimeTypes::getMime($ext);
    }

    /**
     * Detects the mime type of the target file automatically,
     * sends the required headers to trigger a download and
     * outputs the file. Returns false if the mime type could
     * not be determined.
     *
     * @param string $filePath
     * @param string $fileName The name of the file for the client
     * @param bool $asAttachment Whether to force the client to download the file
     * @throws FileHelper_Exception
     */
    public static function sendFile($filePath, $fileName = null, $asAttachment=true)
    {
        if(!file_exists($filePath)) {
            throw new FileHelper_Exception(
                'File does not exist',
                sprintf(
                    'Cannot send the file [%s] to the browser: the file does not exist in the target path. Full path is [%s].',
                    basename($filePath),
                    $filePath
                ),
                self::ERROR_CANNOT_SEND_MISSING_FILE
            );
        }
        
        if (is_null($fileName)) {
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
        exit;
    }

    /**
     * Uses cURL to download the contents of the specified URL,
     * returns the content.
     *
     * @param string $url
     * @throws FileHelper_Exception
     * @return string
     */
    public static function downloadFile($url)
    {
        if (!function_exists('curl_init')) {
            throw new FileHelper_Exception(
                'The cURL extension is not installed.',
                null,
                self::ERROR_CURL_EXTENSION_NOT_INSTALLED
            );
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, APP_URL);
        curl_setopt($ch, CURLOPT_USERAGENT, "Google Chrome/1.0");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
        $output = curl_exec($ch);

        if ($output === false) {
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

        return $output;
    }
    
   /**
    * Verifies whether the target file is a PHP file. The path
    * to the file can be a path to a file as a string, or a 
    * DirectoryIterator object instance.
    * 
    * @param string|DirectoryIterator $pathOrDirIterator
    * @return boolean
    */
    public static function isPHPFile($pathOrDirIterator)
    {
    	if(self::getExtension($pathOrDirIterator) == 'php') {
    		return true;
    	}
    	
    	return false;
    }
    
   /**
    * Retrieves the extension of the specified file. Can be a path
    * to a file as a string, or a DirectoryIterator object instance.
    * 
    * @param string|DirectoryIterator $pathOrDirIterator
    * @param bool $lowercase
    * @return string
    */
    public static function getExtension($pathOrDirIterator, $lowercase = true)
    {
        if($pathOrDirIterator instanceof DirectoryIterator) {
            $filename = $pathOrDirIterator->getFilename();
        } else {
            $filename = basename($pathOrDirIterator);
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
    * @param string|DirectoryIterator $pathOrDirIterator
    * @param bool $extension
    * @return string
    */
    public static function getFilename($pathOrDirIterator, $extension = true)
    {
        $path = $pathOrDirIterator;
    	if($pathOrDirIterator instanceof DirectoryIterator) {
    		$path = $pathOrDirIterator->getFilename();
    	}
    	
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
    * @throws FileHelper_Exception
    * @return array
    */ 
    public static function parseJSONFile($file, $targetEncoding=null, $sourceEncoding=null)
    {
        if(!file_exists($file)) {
            throw new FileHelper_Exception(
                'Cannot find file',
                sprintf(
                    'Tried finding the file [%s], but it does not exist.',
                    $file    
                ),
                self::ERROR_CANNOT_FIND_JSON_FILE
            );
        }
        
        $content = file_get_contents($file);
        if(!$content) {
            throw new FileHelper_Exception(
                'Cannot get file contents',
                sprintf(
                    'The file [%s] exists on disk, but its contents cannot be read.',
                    $file    
                ),
                self::ERROR_JSON_FILE_CANNOT_BE_READ
            );
        }
        
        if(isset($targetEncoding)) {
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
    
    public static function fixFileName($name)
    {
        while(strstr($name, '  ')) {
            $name = str_replace('  ', ' ', $name);
        }
        
        $replaces = array(
            ' .' => '.',
            '. ' => '.',
        );
        
        $name = str_replace(array_keys($replaces), array_values($replaces), $name);
        
        while(strstr($name, '..')) {
            $name = str_replace('..', '.', $name);
        }
        
        return $name;
    }
    
    public static function findHTMLFiles($targetFolder, $options=array())
    {
        return self::findFiles($targetFolder, array('html'), $options);
    }
    
    public static function findPHPFiles($targetFolder, $options=array())
    {
        return self::findFiles($targetFolder, array('php'), $options);
    }
    
    public static function findFiles($targetFolder, $extensions=array(), $options=array(), $files=array())
    {
        if(!isset($options['strip-extension'])) {
            $options['strip-extension'] = false;
        }
        
        if(!isset($options['absolute-path'])) {
            $options['absolute-path'] = false;
        } 
        
        if(!isset($options['relative-path'])) {
            $options['relative-path'] = false;
        }
        
        if(!isset($options['recursive'])) {
            $options['recursive'] = false;
        }
        
        if($options['relative-path']) {
            $options['absolute-path'] = true;
        }
        
        if(!isset($options['__root'])) {
            $options['__root'] = self::normalizePath($targetFolder);
        }
        
        $checkExtensions = false;
        if(!empty($extensions)) {
            $checkExtensions = true;
            $extensions = array_map('strtolower', $extensions);
        }
        
        if(!is_dir($targetFolder)) 
        {
            throw new FileHelper_Exception(
                'Target folder does not exist',
                sprintf(
                    'Cannot find files in folder [%s], it could not be found.',
                    $targetFolder
                ),
                self::ERROR_FIND_FOLDER_DOES_NOT_EXIST
            );
        }
        
        $d = new DirectoryIterator($targetFolder);
        foreach($d as $item) {
            if($item->isDot()) {
                continue;
            }
            
            if($item->isDir()) {
                if($options['recursive']) {
                    $files = self::findFiles($item->getPathname(), $extensions, $options, $files);
                }
                continue;
            }
            
            if($checkExtensions && !in_array(self::getExtension($item, true), $extensions)) {
                continue;
            }
            
            $filename = $item->getFilename();
            if($options['strip-extension']) {
                $filename = self::removeExtension($filename);
            }
            
            if($options['absolute-path']) {
                $filename = self::normalizePath($targetFolder.'/'.$filename);
            }
            
            if($options['relative-path']) {
                $filename = ltrim(str_replace($options['__root'], '', $filename), '/');
            }
            
            $files[] = $filename;
        }
        
        return $files;
    }
    
    public static function removeExtension($filename)
    {
        return pathinfo($filename, PATHINFO_FILENAME);
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
    */
    public static function detectUTFBom($filename) 
    {
        $fp = fopen($filename, 'r');
        $text = fread($fp, 20);
        fclose($fp);

        $boms = self::getUTFBOMs();
        foreach($boms as $bom => $value) {
            $length = mb_strlen($value);
            if(mb_substr($text, 0, $length) == $value) {
                return $bom;
            }
        }
        
        return null;
    }    
    
    protected static $utfBoms;
    
   /**
    * Retrieves a list of all UTF byte order mark character
    * sequences, as an assocative array with UTF encoding => bom sequence
    * pairs.
    * 
    * @return array
    */
    public static function getUTFBOMs()
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
    * unicode encoding, for example "UTF16-LE" or "UTF8"
    * 
    * @param string $encoding
    * @return boolean
    */
    public static function isValidUnicodeEncoding($encoding)
    {
        $encodings = self::getKnownUnicodeEncodings();
        return in_array($encoding, $encodings);
    }
    
   /**
    * Retrieves a list of all known unicode file encodings.
    * @return array
    */
    public static function getKnownUnicodeEncodings()
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
    public static function normalizePath($path)
    {
        if(is_string($path)) {
            $path = str_replace(array('\\', '//'), array('/', '/'), $path);
        }
        
        return $path;
    }
    
    public static function saveAsJSON($data, $file, $pretty=false)
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
        
        if(!file_put_contents($file, $json)) {
            throw new FileHelper_Exception(
                sprintf('Could not write the JSON file [%s] to disk.', basename($file)),
                sprintf('Full path: [%s].', $file),
                self::ERROR_JSON_CANNOT_WRITE_FILE
            );
        }
    }
    
    public static function canMakePHPCalls()
    {
        static $result = null;
        
        if(!isset($result)) {
            $command = 'php -v 2>&1';
            $output = array();
            
            exec($command, $output);
            
            $result = !empty($output);
        }
        
        return $result;
    }
    
    /**
     * Validates a PHP file's syntax.
     * @param string $path
     * @return boolean|array A boolean true if the file is valid, an array with validation messages otherwise.
     */
    public static function checkPHPFileSyntax($path)
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
    public static function getModifiedDate($path)
    {
        $time = filemtime($path);
        if($time !== false) {
            $date = new DateTime();
            $date->setTimestamp($time);
            return $date;
        }
        
        return null; 
    }
    
   /**
    * Retrieves the names of all subfolders in the specified path.
    * 
    * Available options:
    * 
    * - recursive: true/false
    *   Whether to search for subfolders recursively. 
    *   
    * - absolute-paths: true/false
    *   Whether to return a list of absolute paths.
    * 
    * @param string $targetFolder
    * @param array $options
    * @throws FileHelper_Exception
    * @return string[]
    */
    public static function getSubfolders($targetFolder, $options = array())
    {
        if(!is_dir($targetFolder)) 
        {
            throw new FileHelper_Exception(
                'Target folder does not exist',
                sprintf(
                    'Cannot retrieve subfolders from [%s], the folder does not exist.',
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
    * and <code>upload_max_filesize</code>.
    * 
    * @return int Will return <code>-1</code> if no limit.
    */
    public static function getMaxUploadFilesize()
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
    
    protected static function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }
}

class FileHelper_Exception extends Exception
{
    protected $details;
    
    public function __construct($message, $details=null, $code=null, $previous=null)
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }
    
    public function getDetails()
    {
        return $this->details;
    }
}