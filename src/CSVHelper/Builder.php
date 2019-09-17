<?php

namespace AppUtils;

class CSVHelper_Builder
{
    public function getDefaultOptions()
    {
        return array(
            'separatorChar' => ';',
            'trailingNewline' => false
        );
    }
    
    public function setSeparatorChar($char)
    {
        return $this->setOption('separatorChar', $char);
    }
    
    public function setTrailingNewline($useNewline=true)
    {
        return $this->setOption('trailingNewline', $useNewline);
    }

    protected $lines = array();

    /**
     * Adds a line of data keys to be added to the CSV.
     *
     * @param mixed $args,... An array with values, or each entry as a separate argument to addLine().
     * @see renderCSV()
     * @return CSVHelper_Builder
     */
    public function addLine($args)
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }

        $this->lines[] = '"' . implode('"'.$this->getOption('separatorChar').'"', $args) . '"';
        
        return $this;
    }

    /**
     * Renders the CSV from the data lines that have been
     * added by {@link addLine()}.
     *
     * @see addLine()
     * @return string
     */
    public function render()
    {
        $csv = implode(PHP_EOL, $this->lines);

        if($this->getOption('trailingNewline')) {
            $csv .= PHP_EOL;
        }

        return $csv;
    }

    protected $options;
    
    public function setOption($name, $value)
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        $this->options[$name] = $value;
        return $this;
    }
    
    public function setOptions($options)
    {
        foreach($options as $name => $value) {
            $this->setOption($name, $value);
        }
        
        return $this;
    }
    
    public function getOption($name, $default=null)
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        if(isset($this->options[$name])) {
            return $this->options[$name];
        }
        
        return $default;
    }
    
    public function hasOption($name)
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return array_key_exists($name, $this->options);
    }
    
    public function getOptions()
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return $this->options;
    }
    
    public function isOption($name, $value)
    {
        if($this->getOption($name) === $value) {
            return true;
        }
        
        return false;
    }
}

