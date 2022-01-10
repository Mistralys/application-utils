<?php
/**
 * File containing the {@see FileHelper_FileFinder} class.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @see FileHelper_FileFinder
 */

declare(strict_types = 1);

namespace AppUtils;

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
class FileHelper_FileFinder implements Interface_Optionable
{
    use Traits_Optionable;

    public const ERROR_PATH_DOES_NOT_EXIST = 44101;
    
    public const PATH_MODE_ABSOLUTE = 'absolute';
    
    public const PATH_MODE_RELATIVE = 'relative';
    
    public const PATH_MODE_STRIP = 'strip';
    public const OPTION_INCLUDE_EXTENSIONS = 'include-extensions';
    public const OPTION_EXCLUDE_EXTENSIONS = 'exclude-extensions';
    public const OPTION_PATHMODE = 'pathmode';

    /**
    * @var string
    */
    protected $path;
    
   /**
    * @var string[]
    */
    protected $found;
    
   /**
    * The path must exist when the class is instantiated: its
    * real path will be determined to work with.
    * 
    * @param string $path The absolute path to the target folder.
    * @throws FileHelper_Exception
    * @see FileHelper_FileFinder::ERROR_PATH_DOES_NOT_EXIST
    */
    public function __construct(string $path)
    {
        $real = realpath($path);
        
        if($real === false) 
        {
            throw new FileHelper_Exception(
                'Target path does not exist',
                sprintf(
                    'Tried accessing path [%s], but its real path could not be determined.',
                    $path
                ),
                self::ERROR_PATH_DOES_NOT_EXIST
            );
        }
        
        $this->path = FileHelper::normalizePath($real);
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
    * @return FileHelper_FileFinder
    */
    public function stripExtensions() : FileHelper_FileFinder
    {
        return $this->setOption('strip-extensions', true);
    }
    
   /**
    * Enables recursing into subfolders.
    * 
    * @return FileHelper_FileFinder
    */
    public function makeRecursive() : FileHelper_FileFinder
    {
        return $this->setOption('recursive', true);
    }
    
   /**
    * Retrieves all extensions that were added to
    * the include list.
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
    * @return FileHelper_FileFinder
    * @see FileHelper_FileFinder::includeExtensions()
    */
    public function includeExtension(string $extension) : FileHelper_FileFinder
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
    * @return FileHelper_FileFinder
    * @see FileHelper_FileFinder::includeExtension()
    */
    public function includeExtensions(array $extensions) : FileHelper_FileFinder
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
    * @return FileHelper_FileFinder
    * @see FileHelper_FileFinder::excludeExtensions()
    */
    public function excludeExtension(string $extension) : FileHelper_FileFinder
    {
        return $this->excludeExtensions(array($extension));
    }

   /**
    * Add several extensions to the list of extensions to
    * exclude from the file search.
    *  
    * @param string[] $extensions Extension names, without dot (`php` for example).
    * @return FileHelper_FileFinder
    * @see FileHelper_FileFinder::excludeExtension()
    */
    public function excludeExtensions(array $extensions) : FileHelper_FileFinder
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
    * @return FileHelper_FileFinder
    */
    public function setPathmodeStrip() : FileHelper_FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_STRIP);
    }
    
   /**
    * In this mode, only the path relative to the source folder
    * will be included in the files list.
    * 
    * @return FileHelper_FileFinder
    */
    public function setPathmodeRelative() : FileHelper_FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_RELATIVE);
    }
    
   /**
    * In this mode, the full, absolute paths to the files will
    * be included in the files list.
    * 
    * @return FileHelper_FileFinder
    */
    public function setPathmodeAbsolute() : FileHelper_FileFinder
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
    * @return FileHelper_FileFinder
    */
    public function setSlashReplacement(string $character) : FileHelper_FileFinder
    {
        return $this->setOption('slash-replacement', $character);
    }


    /**
     * Sets how paths should be handled in the file names
     * that are returned.
     *
     * @param string $mode
     * @return FileHelper_FileFinder
     *
     * @see FileHelper_FileFinder::PATH_MODE_ABSOLUTE
     * @see FileHelper_FileFinder::PATH_MODE_RELATIVE
     * @see FileHelper_FileFinder::PATH_MODE_STRIP
     */
    protected function setPathmode(string $mode) : FileHelper_FileFinder
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
        $this->find($this->path, true);
        
        return $this->found;
    }
    
   /**
    * Retrieves only PHP files. Can be combined with other
    * options like enabling recursion into subfolders.
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
        
        $d = new \DirectoryIterator($path);
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
            if(!in_array($extension, $include)) {
                return false;
            }
        }
        else if(!empty($exclude))
        {
            if(in_array($extension, $exclude)) {
                return false;
            }
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
