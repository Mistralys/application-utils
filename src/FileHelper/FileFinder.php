<?php
/**
 * File containing the {@see \AppUtils\FileHelper\FileFinder} class.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\FileFinder
 */

declare(strict_types = 1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\Interface_Optionable;
use AppUtils\Traits_Optionable;
use DirectoryIterator;

/**
 * File finder class used to fetch file lists from folders,
 * with criteria matching. Offers many customization options
 * on how to return the files, from absolute paths to file names
 * without extensions or even class name maps.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileFinder implements Interface_Optionable
{
    use Traits_Optionable;

    public const ERROR_PATH_DOES_NOT_EXIST = 44101;
    
    public const PATH_MODE_ABSOLUTE = 'absolute';
    public const PATH_MODE_RELATIVE = 'relative';
    public const PATH_MODE_STRIP = 'strip';

    public const OPTION_INCLUDE_EXTENSIONS = 'include-extensions';
    public const OPTION_EXCLUDE_EXTENSIONS = 'exclude-extensions';
    public const OPTION_PATHMODE = 'pathmode';

    protected FolderInfo $path;
    
   /**
    * @var string[]
    */
    protected array $found = array();
    
   /**
    * The path must exist when the class is instantiated: its
    * real path will be determined to work with.
    * 
    * @param string|PathInfoInterface|DirectoryIterator $path The absolute path to the target folder.
    *
    * @throws FileHelper_Exception
    * @see FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
    */
    public function __construct($path)
    {
        $this->path = AbstractPathInfo::resolveType($path)->requireIsFolder();
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'recursive' => false,
            'strip-extensions' => false,
            self::OPTION_INCLUDE_EXTENSIONS => array(),
            self::OPTION_EXCLUDE_EXTENSIONS => array(),
            self::OPTION_PATHMODE => self::PATH_MODE_ABSOLUTE,
            'slash-replacement' => null
        );
    }
    
   /**
    * Enables extension stripping, to return file names without extension.
    * 
    * @return FileFinder
    */
    public function stripExtensions() : FileFinder
    {
        return $this->setOption('strip-extensions', true);
    }

    /**
     * Enables recursion into sub-folders.
     *
     * @param bool $enabled
     * @return FileFinder
     */
    public function makeRecursive(bool $enabled=true) : FileFinder
    {
        return $this->setOption('recursive', $enabled);
    }
    
   /**
    * Retrieves all extensions that were added to
    * the list of included extensions.
    * 
    * @return string[]
    */
    public function getIncludeExtensions() : array
    {
        return $this->getArrayOption(self::OPTION_INCLUDE_EXTENSIONS);
    }
    
   /**
    * Includes a single extension in the file search: only
    * files with this extension will be used in the results.
    * 
    * NOTE: Included extensions take precedence before excluded
    * extensions. If any excluded extensions are specified, they
    * will be ignored.
    * 
    * @param string $extension Extension name, without dot (`php` for example).
    * @return FileFinder
    * @see FileFinder::includeExtensions()
    */
    public function includeExtension(string $extension) : FileFinder
    {
        return $this->includeExtensions(array($extension));
    }
    
   /**
    * Includes several extensions in the file search: only
    * files with these extensions wil be used in the results.
    * 
    * NOTE: Included extensions take precedence before excluded
    * extensions. If any excluded extensions are specified, they
    * will be ignored.
    * 
    * @param string[] $extensions Extension names, without dot (`php` for example).
    * @return FileFinder
    * @see FileFinder::includeExtension()
    */
    public function includeExtensions(array $extensions) : FileFinder
    {
        $items = $this->getIncludeExtensions();
        $items = array_merge($items, $extensions);
        $items = array_unique($items);
        
        $this->setOption(self::OPTION_INCLUDE_EXTENSIONS, $items);
        return $this;
    }

   /**
    * Retrieves a list of all extensions currently set as 
    * excluded from the search.
    * 
    * @return string[]
    */
    public function getExcludeExtensions() : array
    {
        return $this->getArrayOption(self::OPTION_EXCLUDE_EXTENSIONS);
    }
    
   /**
    * Excludes a single extension from the search.
    * 
    * @param string $extension Extension name, without dot (`php` for example).
    * @return FileFinder
    * @see FileFinder::excludeExtensions()
    */
    public function excludeExtension(string $extension) : FileFinder
    {
        return $this->excludeExtensions(array($extension));
    }

   /**
    * Add several extensions to the list of extensions to
    * exclude from the file search.
    *  
    * @param string[] $extensions Extension names, without dot (`php` for example).
    * @return FileFinder
    * @see FileFinder::excludeExtension()
    */
    public function excludeExtensions(array $extensions) : FileFinder
    {
        $items = $this->getExcludeExtensions();
        $items = array_merge($items, $extensions);
        $items = array_unique($items);
        
        $this->setOption(self::OPTION_EXCLUDE_EXTENSIONS, $items);
        return $this;
    }
    
   /**
    * In this mode, the entire path to the file will be stripped,
    * leaving only the file name in the files list.
    * 
    * @return FileFinder
    */
    public function setPathmodeStrip() : FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_STRIP);
    }
    
   /**
    * In this mode, only the path relative to the source folder
    * will be included in the files list.
    * 
    * @return FileFinder
    */
    public function setPathmodeRelative() : FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_RELATIVE);
    }
    
   /**
    * In this mode, the full, absolute paths to the files will
    * be included in the files list.
    * 
    * @return FileFinder
    */
    public function setPathmodeAbsolute() : FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_ABSOLUTE);
    }
    
   /**
    * This sets a character or string to replace the slashes
    * in the paths with. 
    * 
    * This is used for example in the `getPHPClassNames()` 
    * method, to return files from subfolders as class names
    * using the "_" character:
    * 
    * Subfolder/To/File.php => Subfolder_To_File.php
    * 
    * @param string $character
    * @return FileFinder
    */
    public function setSlashReplacement(string $character) : FileFinder
    {
        return $this->setOption('slash-replacement', $character);
    }


    /**
     * Sets how paths should be handled in the file names
     * that are returned.
     *
     * @param string $mode
     * @return FileFinder
     *
     * @see FileFinder::PATH_MODE_ABSOLUTE
     * @see FileFinder::PATH_MODE_RELATIVE
     * @see FileFinder::PATH_MODE_STRIP
     */
    protected function setPathmode(string $mode) : FileFinder
    {
        return $this->setOption(self::OPTION_PATHMODE, $mode);
    }
    
   /**
    * Retrieves a list of all matching file names/paths,
    * depending on the selected options.
    * 
    * @return string[]
    */
    public function getAll() : array
    {
        $this->find((string)$this->path, true);
        
        return $this->found;
    }
    
   /**
    * Retrieves only PHP files. Can be combined with other
    * options like enabling recursion into sub-folders.
    * 
    * @return string[]
    */
    public function getPHPFiles() : array
    {
        $this->includeExtensions(array('php'));
        return $this->getAll();
    }
    
   /**
    * Generates PHP class names from file paths: it replaces
    * slashes with underscores, and removes file extensions.
    * 
    * @return string[] An array of PHP file names without extension.
    */
    public function getPHPClassNames() : array
    {
        $this->includeExtensions(array('php'));
        $this->stripExtensions();
        $this->setSlashReplacement('_');
        $this->setPathmodeRelative();
        
        return $this->getAll();
    }
    
    protected function find(string $path, bool $isRoot=false) : void
    {
        if($isRoot) {
            $this->found = array();
        }
        
        $recursive = $this->getBoolOption('recursive');
        
        $d = new DirectoryIterator($path);
        foreach($d as $item)
        {
            $pathname = $item->getPathname();
            
            if($item->isDir())
            {
                if($recursive && !$item->isDot()) {
                    $this->find($pathname);
                }
                
                continue;
            }
            
            $file = $this->filterFile($pathname);
            
            if($file !== null) 
            {
                $this->found[] = $file;
            }
        }
    }
    
    protected function filterFile(string $path) : ?string
    {
        $path = FileHelper::normalizePath($path);
        
        $extension = FileHelper::getExtension($path);
        
        if(!$this->filterExclusion($extension)) {
            return null;
        }
        
        $path = $this->filterPath($path);
        
        if($this->getOption('strip-extensions') === true)
        {
            $path = str_replace('.'.$extension, '', $path);
        }
        
        if($path === '') {
            return null;
        }
        
        $replace = $this->getOption('slash-replacement');
        if(!empty($replace)) {
            $path = str_replace('/', $replace, $path);
        }
        
        return $path;
    }
    
   /**
    * Checks whether the specified extension is allowed 
    * with the current settings.
    * 
    * @param string $extension
    * @return bool
    */
    protected function filterExclusion(string $extension) : bool
    {
        $include = $this->getOption(self::OPTION_INCLUDE_EXTENSIONS);
        $exclude = $this->getOption(self::OPTION_EXCLUDE_EXTENSIONS);
        
        if(!empty($include))
        {
            if(!in_array($extension, $include, true)) {
                return false;
            }
        }
        else if(!empty($exclude) && in_array($extension, $exclude, true))
        {
            return false;
        }
        
        return true;
    }
    
   /**
    * Adjusts the path according to the selected path mode.
    * 
    * @param string $path
    * @return string
    */
    protected function filterPath(string $path) : string
    {
        switch($this->getStringOption(self::OPTION_PATHMODE))
        {
            case self::PATH_MODE_STRIP:
                return basename($path);
                
            case self::PATH_MODE_RELATIVE:
                $path = str_replace($this->path, '', $path);
                return ltrim($path, '/');
        }
        
        return $path;
    }
}
