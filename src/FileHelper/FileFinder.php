<?php

declare(strict_types = 1);

namespace AppUtils;

class FileHelper_FileFinder
{
    const PATH_MODE_ABSOLUTE = 'absolute';
    
    const PATH_MODE_RELATIVE = 'relative';
    
    const PATH_MODE_STRIP = 'strip';
    
   /**
    * @var string
    */
    protected $path;
    
    protected $options = array(
        'recursive' => false,
        'strip-extensions' => false,
        'include-extensions' => array(),
        'exclude-extensions' => array(),
        'pathmode' => self::PATH_MODE_ABSOLUTE,
        'slash-replacement' => null
    );
    
    public function __construct(string $path)
    {
        $this->path = $this->normalizeSlashes($path);
    }
    
    protected function normalizeSlashes($string)
    {
        return str_replace('\\', '/', $string);
    }
    
    public function stripExtensions() : FileHelper_FileFinder
    {
        return $this->setOption('strip-extensions', true);
    }
    
    public function makeRecursive() : FileHelper_FileFinder
    {
        return $this->setOption('recursive', true);
    }
    
    public function includeExtensions(array $extensions) : FileHelper_FileFinder
    {
        $items = $this->getOption('include-extensions');
        $items = array_merge($items, $extensions);
        $items = array_unique($items);
        
        $this->setOption('include-extensions', $items);
        return $this;
    }
    
    public function excludeExtensions(array $extensions) : FileHelper_FileFinder
    {
        $items = $this->getOption('exclude-extensions');
        $items = array_merge($items, $extensions);
        $items = array_unique($items);
        
        $this->setOption('exclude-extensions', $items);
        return $this;
    }
    
    public function setPathmodeStrip() : FileHelper_FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_STRIP);
    }
    
    public function setPathmodeRelative() : FileHelper_FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_RELATIVE);
    }
    
    public function setPathmodeAbsolute() : FileHelper_FileFinder
    {
        return $this->setPathmode(self::PATH_MODE_ABSOLUTE);
    }
    
    public function setSlashReplacement($character)
    {
        return $this->setOption('slash-replacement', $character);
    }
    
    protected function setPathmode($mode) : FileHelper_FileFinder
    {
        return $this->setOption('pathmode', $mode);
    }
    
    protected function setOption($name, $value) : FileHelper_FileFinder
    {
        $this->options[$name] = $value;
        return $this;
    }
    
    protected function getOption($name, $default=null)
    {
        if(isset($this->options[$name])) {
            return $this->options[$name];
        }
        
        return $default;
    }
    
    public function getAll() : array
    {
        if(!isset($this->found)) {
            $this->find($this->path, true);
        }
        
        return $this->found;
    }
    
    public function getPHPFiles() : array
    {
        $this->includeExtensions(array('php'));
        return $this->getAll();
    }
    
    public function getPHPClassNames() : array
    {
        $this->includeExtensions(array('php'));
        $this->stripExtensions();
        $this->setSlashReplacement('_');
        $this->setPathmodeRelative();
        
        return $this->getAll();
    }
    
    protected $found;
    
    protected function find($path, $isRoot=false)
    {
        if($isRoot) {
            $this->found = array();
        }
        
        $d = new \DirectoryIterator($path);
        foreach($d as $item)
        {
            if($item->isDir())
            {
                if($this->getOption('recursive') === true && !$item->isDot()) {
                    $this->find($item->getPathname());
                }
            }
            else
            {
                $file = $this->filterFile($item->getPathname());
                if($file) {
                    $this->found[] = $file;
                }
            }
        }
    }
    
    protected function filterFile($path)
    {
        $path = $this->normalizeSlashes($path);
        
        $info = pathinfo($path);
        
        $include = $this->getOption('include-extensions');
        $exclude = $this->getOption('exclude-extensions');
        
        if(!empty($include))
        {
            if(!in_array($info['extension'], $include)) {
                return false;
            }
        }
        else if(!empty($exclude))
        {
            if(in_array($info['extension'], $exclude)) {
                return false;
            }
        }
        
        switch($this->getOption('pathmode'))
        {
            case self::PATH_MODE_STRIP:
                $path = basename($path);
                break;
                
            case self::PATH_MODE_RELATIVE:
                $path = str_replace($this->path, '', $path);
                $path = ltrim($path, '/');
                break;
                
            case self::PATH_MODE_ABSOLUTE:
            default:
                break;
        }
        
        if($this->getOption('strip-extensions') === true)
        {
            $path = str_replace('.'.$info['extension'], '', $path);
        }
        
        $replace = $this->getOption('slash-replacement');
        if(!empty($replace)) {
            $path = str_replace('/', $replace, $path);
        }
        
        return $path;
    }
}