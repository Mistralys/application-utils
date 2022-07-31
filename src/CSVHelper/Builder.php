<?php
/**
 * File containing the {@see AppUtils\CSVHelper_Builder} class.
 *
 * @package Application Utils
 * @subpackage CSVHelper
 * @see AppUtils\CSVHelper_Builder
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * The CSV builder allows creating CSV contents 
 * with an object-oriented interface.
 *
 * @package Application Utils
 * @subpackage CSVHelper
 */
class CSVHelper_Builder
{
    /**
     * @var string[]
     */
    protected array $lines = array();

    /**
     * @var array<string,mixed>|NULL
     */
    protected ?array $options = null;

    /**
     * @return array<string,mixed>
     */
    public function getDefaultOptions() : array
    {
        return array(
            'separatorChar' => ';',
            'trailingNewline' => false
        );
    }
    
    public function setSeparatorChar(string $char) : self
    {
        return $this->setOption('separatorChar', $char);
    }

    public function setTrailingNewline(bool $useNewline=true) : self
    {
        return $this->setOption('trailingNewline', $useNewline);
    }

    /**
     * Adds a line of data keys to be added to the CSV.
     *
     * @param mixed ...$args An array with values, or each entry as a separate argument to addLine().
     * @see renderCSV()
     * @return $this
     */
    public function addLine(...$args) : self
    {
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
    public function render() : string
    {
        $csv = implode(PHP_EOL, $this->lines);

        if($this->getOption('trailingNewline')) {
            $csv .= PHP_EOL;
        }

        return $csv;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setOption(string $name, $value) : self
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @param array<string,mixed> $options
     * @return $this
     */
    public function setOptions(array $options) : self
    {
        foreach($options as $name => $value) {
            $this->setOption($name, $value);
        }
        
        return $this;
    }

    /**
     * @param string $name
     * @param mixed|NULL $default
     * @return mixed|NULL
     */
    public function getOption(string $name, $default=null)
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }

        return $this->options[$name] ?? $default;
    }
    
    public function hasOption(string $name) : bool
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return array_key_exists($name, $this->options);
    }

    /**
     * @return array<string,mixed>
     */
    public function getOptions() : array
    {
        if(!isset($this->options)) {
            $this->options = $this->getDefaultOptions();
        }
        
        return $this->options;
    }

    /**
     * @param string $name
     * @param mixed|NULL $value
     * @return bool
     */
    public function isOption(string $name, $value) : bool
    {
        return $this->getOption($name) === $value;
    }
}
