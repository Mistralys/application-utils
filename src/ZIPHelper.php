<?php
/**
 * File containing the {@link ZIPHelper} class.
 * @package Application Utils
 * @subpackage ZIPHelper
 * @see ZIPHelper
 */

namespace AppUtils;

use ZipArchive;

/**
 * ZIP helper class to simplify working with the native 
 * PHP ZIPArchive functions.
 * 
 * Usage:
 * 
 * <pre>
 * $zip = new ZIPHelper('ziparchive.zip');
 * </pre>
 * 
 * @package Application Utils
 * @subpackage ZIPHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ZIPHelper
{
    const ERROR_SOURCE_FILE_DOES_NOT_EXIST = 338001;
    
    const ERROR_NO_FILES_IN_ARCHIVE = 338002;
    
    const ERROR_OPENING_ZIP_FILE = 338003;
    
    const ERROR_CANNOT_SAVE_FILE_TO_DISK =338004;
    
    protected $options = array(
        'WriteThreshold' => 100
    );
    
   /**
    * @var string
    */
    protected $file;
    
   /**
    * @var ZipArchive|NULL
    */
    protected $zip;
    
    public function __construct(string $targetFile)
    {
        $this->file = $targetFile;
    }
    
   /**
    * Sets an option, among the available options:
    * 
    * <ul>
    * <li>WriteThreshold: The amount of files to add before the zip is automatically written to disk and re-opened to release the file handles. Set to 0 to disable.</li>
    * </ul>
    * 
    * @param string $name
    * @param mixed $value
    * @return ZIPHelper
    */
    public function setOption(string $name, $value) : ZIPHelper
    {
        $this->options[$name] = $value;
        return $this;
    }
    
   /**
    * Adds a file to the zip. By default, the file is stored
    * with the same name in the root of the zip. Use the optional
    * parameter to change the location in the zip.
    * 
    * @param string $filePath
    * @param string|null $zipPath If no path is specified, file will be added with the same name in the ZIP's root.
    * @throws ZIPHelper_Exception
    * @return bool
    * 
    * @see FileHelper::ERROR_SOURCE_FILE_DOES_NOT_EXIST
    */
    public function addFile(string $filePath, ?string $zipPath=null) : bool
    {
        $this->open();
        
        if (!file_exists($filePath) || !is_file($filePath)) 
        {
            throw new ZIPHelper_Exception(
                'File not found or not a file',
                sprintf(
                    'Tried adding the file [%1$s] to the zip file, but it does not exist, or it is not a file.',
                    $filePath
                ),
                self::ERROR_SOURCE_FILE_DOES_NOT_EXIST
            );
        }
        
        if (empty($zipPath)) {
            $zipPath = basename($filePath);
        }
        
        $result = $this->zip->addFile($filePath, $zipPath);
        
        $this->releaseFileHandles();
        
        return $result;
    }

    /**
     * Uses the specified string as content for a file to add to
     * the archive.
     *
     * @param string $contents The contents of the file.
     * @param string $zipPath The filename, or relative path within the archive.
     * @return bool
     */
    public function addString(string $contents, string $zipPath) : bool
    {
        $this->open();
        
        return $this->zip->addFromString($zipPath, $contents);
    }

    /**
     * @var bool
     */
    protected $open = false;

    /**
     * @throws ZIPHelper_Exception
     * @see ZIPHelper::ERROR_OPENING_ZIP_FILE
     */
    protected function open() : void
    {
        if($this->open) {
            return;
        }
        
        if(!isset($this->zip)) {
            $this->zip = new ZipArchive();
        }
        
        $flag = null;
        if(!file_exists($this->file)) {
            $flag = ZipArchive::CREATE;
        }
        
        if ($this->zip->open($this->file, $flag) !== true) {
            throw new ZIPHelper_Exception(
                'Cannot open ZIP file',
                sprintf(
                    'Opening the ZIP file [%1$s] failed.',
                    $this->file
                ),
                self::ERROR_OPENING_ZIP_FILE
            );
        }
        
        $this->open = true;
    }

    /**
     * @var int
     */
    protected $fileTracker = 0;

    /**
     * Checks whether the file handles currently open for the
     * zip file have to be released. This is called for every
     * file that gets added to the file.
     *
     * With a large amount of files being added to the zip file, it is
     * possible to reach the limit of the amount of file handles open
     * at the same time: This is because PHP locks every file that gets
     * added to the ZIP, until the ZIP is written to disk.
     *
     * To counter this problem, we simply write the ZIP every X files
     * added to it, so the file handles get released.
     *
     * @see addFileToZip()
     * @see $zipWriteThreshold
     */
    protected function releaseFileHandles() : void
    {
        $this->fileTracker++;

        if($this->options['WriteThreshold'] < 1) {
            return;
        }
        
        if ($this->fileTracker >= $this->options['WriteThreshold']) {
            $this->close();
            $this->open();
            $this->fileTracker = 0;
        }
    }
    
    protected function close() : void
    {
        if(!$this->open) {
            return;
        }
        
        if (!$this->zip->close()) 
        {
            throw new ZIPHelper_Exception(
                'Could not save ZIP file to disk',
                sprintf(
                    'Tried saving the ZIP file [%1$s], but the write failed. This can have several causes, ' .
                    'including adding files that do not exist on disk, trying to create an empty zip, ' .
                    'or trying to save to a directory that does not exist.',
                    $this->file
                ),
                self::ERROR_CANNOT_SAVE_FILE_TO_DISK
            );
        }
        
        $this->open = false;
    }
    
    public function save() : ZIPHelper
    {
        $this->open();
        
        if($this->countFiles() < 1) 
        {
            throw new ZIPHelper_Exception(
                'No files in the zip file',
                sprintf(
                    'No files were added to the zip file [%1$s], cannot save it without any files.',
                    $this->file
                ),
                self::ERROR_NO_FILES_IN_ARCHIVE
            );
        }
        
        $this->close();

        return $this;
    }

    /**
     * Writes the active ZIP file to disk, and sends headers for
     * the client to download it.
     *
     * @param string|NULL $fileName Override the ZIP's file name for the download
     * @see ZIPHelper::downloadAndDelete()
     * @throws ZIPHelper_Exception
     * @return string The file name that was sent (useful in case none was specified).
     */
    public function download(?string $fileName=null) : string
    {
        $this->save();
        
        if(empty($fileName))
        {
            $fileName = basename($this->file);
        }
        
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-length: ' . filesize($this->file));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($this->file);
        
        return $fileName;
    }

    /**
     * Like {@link ZIPHelper::download()}, but deletes the
     * file after sending it to the browser.
     *
     * @param string|NULL $fileName Override the ZIP's file name for the download
     * @return $this
     * @throws FileHelper_Exception
     * @throws ZIPHelper_Exception
     * @see ZIPHelper::download()
     */
    public function downloadAndDelete(?string $fileName=null) : ZIPHelper
    {
        $this->download($fileName);
        
        FileHelper::deleteFile($this->file);

        return $this;
    }

   /**
    * Extracts all files and folders from the zip to the 
    * target folder. If no folder is specified, the files
    * are extracted into the same folder as the zip itself.
    * 
    * @param string|NULL $outputFolder If no folder is specified, uses the target file's folder.
    * @return boolean
    */
    public function extractAll(?string $outputFolder=null) : bool
    {
        if(empty($outputFolder)) {
            $outputFolder = dirname($this->file);
        }
        
        $this->open();
        
        return $this->zip->extractTo($outputFolder);
    }

   /**
    * @return ZipArchive
    */
    public function getArchive() : ZipArchive
    {
        $this->open();
        
        return $this->zip;
    }
    
   /**
    * JSON encodes the specified data and adds the json as
    * a file in the ZIP archive.
    * 
    * @param mixed $data
    * @param string $zipPath
    * @return boolean
    */
    public function addJSON($data, string $zipPath) : bool
    {
        return $this->addString(
            json_encode($data, JSON_THROW_ON_ERROR),
            $zipPath
        );
    }

    /**
     * Counts the amount of files currently present in the archive.
     * @return int
     */
    public function countFiles() : int
    {
        $this->open();
        
        return (int)$this->zip->numFiles;
    }
}
