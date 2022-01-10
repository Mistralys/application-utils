<?php
/**
 * File containing the {@link CSVHelper} class.
 * 
 * @package Application Utils
 * @subpackage CSVHelper
 * @see CSVHelper
 */

namespace AppUtils;

use ParseCsv\Csv;

/**
 * Helper class to parse and create/modify csv files or strings.
 *
 * Usage:
 * 
 * ```php
 * $csv = new CSVHelper();
 * $csv->setHeadersTop(); // has to be set before anything else.
 * 
 * // parse a csv file
 * $csv->loadFile('path/to/file');
 * 
 * // parse a csv string
 * $csv->loadString($csvString);
 * 
 * // retrieve data
 * $headers = $csv->getHeaders();
 * $row = $csv->getRow(4);
 * ```
 *
 * @package Application Utils
 * @subpackage CSVHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CSVHelper
{
    public const ERROR_INVALID_HEADERS_POSITION = 561002;
    public const ERROR_INVALID_FILE_ENCODING = 561003;
    public const ERROR_FILE_PARSING_FAILED = 561004;
    public const ERROR_CSV_FILE_NOT_READABLE = 561005;
    public const ERROR_STRING_PARSING_FAILED = 561006;

    public const DELIMITER_AUTO = 'auto';

    public const HEADERS_LEFT = 'hleft';
    public const HEADERS_TOP = 'htop';
    public const HEADERS_NONE = 'hnone';
    
    public function __construct()
    {
        
    }

   /**
    * Creates and returns a new instance of the CSV builder which
    * can be used to build CSV from scratch.
    * 
    * @return CSVHelper_Builder
    */
    public static function createBuilder()
    {
        return new CSVHelper_Builder();
    }

   /**
    * @var string
    */
    protected $csv = '';
    
    protected $data = array();
    
    protected $headers = array();
    
    protected $headersPosition = self::HEADERS_NONE;
    
   /**
    * Loads CSV data from a string. 
    * 
    * Note: Use the {@link hasErrors()} method to 
    * check if the string could be parsed correctly
    * afterwards.
    * 
    * @param string $string
    * @return CSVHelper
    */
    public function loadString($string)
    {
        // remove any UTF byte order marks that may still be present in the string
        $this->csv = ConvertHelper::stripUTFBom($string);

        // ensure the string is valid UTF8
        $this->csv = ConvertHelper::string2utf8($this->csv);
        
        $this->parse();
        
        return $this;
    }
    
   /**
    * Loads CSV data from a file.
    * 
    * Note: Use the {@link hasErrors()} method to 
    * check if the string could be parsed correctly
    * afterwards.
    * 
    * @param string $file
    * @throws FileHelper_Exception
    * @return CSVHelper
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    * @see FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
    */
    public function loadFile(string $file) : CSVHelper
    {
        $csv = FileHelper::readContents($file);
        
        return $this->loadString($csv);
    }
    
    protected $errors = array();
    
    protected $columnCount = 0;
    
    protected $rowCount = 0;
    
   /**
    * Specifies that headers are positioned on top, horizontally.
    * @return CSVHelper
    */
    public function setHeadersTop()
    {
        return $this->setHeadersPosition(self::HEADERS_TOP);
    }
    
   /**
    * Specifies that headers are positioned on the left, vertically.
    * @return CSVHelper
    */
    public function setHeadersLeft()
    {
        return $this->setHeadersPosition(self::HEADERS_LEFT);
    }
    
   /**
    * Specifies that there are no headers in the file (default).
    * @return CSVHelper
    */
    public function setHeadersNone()
    {
        return $this->setHeadersPosition(self::HEADERS_NONE);
    }
    
    public function isHeadersLeft()
    {
        return $this->isHeadersPosition(self::HEADERS_LEFT);
    }
    
    public function isHeadersTop()
    {
        return $this->isHeadersPosition(self::HEADERS_TOP);
    }
    
    public function isHeadersNone()
    {
        return $this->isHeadersPosition(self::HEADERS_NONE);
    }
    
    public function isHeadersPosition($position)
    {
        if($this->headersPosition === $position) {
            return true;
        }
        
        return false;
    }
    
   /**
    * Specifies where the headers are positioned in the
    * CSV, or turns them off entirely. Use the class constants
    * to ensure the value is correct.
    * 
    * @param string $position
    * @throws CSVHelper_Exception
    * @return CSVHelper
    * @see CSVHelper::HEADERS_LEFT
    * @see CSVHelper::HEADERS_TOP
    * @see CSVHelper::HEADERS_NONE
    */
    public function setHeadersPosition($position)
    {
        $validPositions = array(
            self::HEADERS_LEFT, 
            self::HEADERS_NONE, 
            self::HEADERS_TOP
        );
        
        if(!in_array($position, $validPositions)) {
            throw new CSVHelper_Exception(
                'Invalid headers position',
                sprintf(
                    'The header position [%s] is invalid. Valid positions are [%s]. '.
                    'It is recommended to use the class constants, for example [%s].',
                    $position,
                    implode(', ', $validPositions),
                    'CSVHelper::HEADERS_LEFT'
                ),
                self::ERROR_INVALID_HEADERS_POSITION
            );
        }
        
        $this->headersPosition = $position;
        return $this;
    }
    
   /**
    * Resets all internal data, allowing to start entirely anew
    * with a new file, or to start building a new CSV file from
    * scratch.
    * 
    * @return CSVHelper
    */
    public function reset()
    {
        $this->data = array();
        $this->headers = array();
        $this->errors = array();
        $this->columnCount = 0;
        $this->rowCount = 0;
        
        return $this;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
   /**
    * Retrieves the row at the specified index.
    * If there is no data at the index, this will
    * return an array populated with empty strings
    * for all available columns.
    * 
    * Tip: Use the {@link rowExists()} method to check
    * whether the specified row exists.
    * 
    * @param integer $index
    * @return array()
    * @see rowExists()
    */
    public function getRow($index)
    {
        if(isset($this->data[$index])) {
            return $this->data[$index];
        }
        
        return array_fill(0, $this->rowCount, '');
    }
    
   /**
    * Checks whether the specified row exists in the data set.
    * @param integer $index
    * @return boolean
    */
    public function rowExists($index)
    {
        return isset($this->data[$index]);
    }
    
   /**
    * Counts the amount of rows in the parsed CSV,
    * excluding the headers if any, depending on 
    * their position.
    * 
    * @return integer
    */
    public function countRows()
    {
        return $this->rowCount;
    }
    
   /**
    * Counts the amount of rows in the parsed CSV, 
    * excluding the headers if any, depending on
    * their position.
    * 
    * @return integer
    */
    public function countColumns()
    {
        return $this->columnCount;
    }
    
   /**
    * Retrieves the headers, if any. Specify the position of the
    * headers first to ensure this works correctly.
    * 
    * @return array Indexed array with header names.
    */
    public function getHeaders()
    {
        return $this->headers;
    }
    
   /**
    * Retrieves the column at the specified index. If there
    * is no column at the index, this returns an array
    * populated with empty strings.
    * 
    * Tip: Use the {@link columnExists()} method to check
    * whether a column exists.
    * 
    * @param integer $index
    * @return string[]
    * @see columnExists()
    */
    public function getColumn($index)
    {
        $data = array();
        for($i=0; $i < $this->rowCount; $i++) {
            $value = '';
            if(isset($this->data[$i][$index])) {
                $value = $this->data[$i][$index];
            }
            
            $data[] = $value;
        }
        
        return $data;
    }
    
   /**
    * Checks whether the specified column exists in the data set.
    * @param integer $index
    * @return boolean
    */
    public function columnExists($index)
    {
        if($index < $this->columnCount) {
            return true;
        }
        
        return false;
    }
    
    protected function parse()
    {
        $this->reset();
        
        if(empty(trim($this->csv))) {
            $this->addError('Tried to parse an empty CSV string.');
            return;
        }
        
        // ensure that the last line in the CSV has
        // a linebreak afterwards, otherwise the line
        // will not be parsed.
        $this->csv = rtrim($this->csv).PHP_EOL;
        
        $parser = self::createParser();

        if(!$parser->parse($this->csv)) {
            $this->addError('The CSV string could not be parsed.');
            return;
        }

        $result = $parser->data;

        switch($this->headersPosition)
        {
            case self::HEADERS_TOP:
                $this->headers = array_shift($result);
                break;
                
            case self::HEADERS_LEFT:
                $keep = array();
                $total = count($result);
                for($i=0; $i < $total; $i++) {
                    $row = $result[$i];
                    $this->headers[] = array_shift($row);
                    $keep[] = $row;
                }
                
                $result = $keep;
                break;
        }
        
        $this->data = $result;
        $this->rowCount = count($this->data);
        
        for($i=0; $i < $this->rowCount; $i++) {
            $amount = count($this->data[$i]);
            if($amount > $this->columnCount) {
                $this->columnCount = $amount;
            }
        }
    }
    
   /**
    * Checks whether any errors have been encountered
    * while parsing the CSV.
    * 
    * @return boolean
    * @see getErrorMessages()
    */
    public function hasErrors()
    {
        return !empty($this->errors);
    }
    
   /**
    * Retrieves all error messages.
    * @return array
    */
    public function getErrorMessages()
    {
        return $this->errors;
    }
    
    protected function addError($error)
    {
        $this->errors[] = $error;
    }
    
    protected $separator = ';';
    
    protected function detectSeparator()
    {
        $search = array(
            "\"\t\"" => "\t",
            '";"' => ';',
            '","' => ',',
            ';;' => ';',
            ',,' => ','
        );
        
        foreach($search as $char => $separator) {
            if(strstr($this->csv, $char)) {
                return $separator;
            }
        }
        
        return $this->separator;
    }

    /**
     * Creates a new CSV parser instance.
     *
     * @param string $delimiter
     * @return Csv
     */
    public static function createParser(string $delimiter=self::DELIMITER_AUTO) : Csv
    {
        $csv = new Csv();

        if($delimiter !== self::DELIMITER_AUTO) {
            $csv->delimiter = $delimiter;
        }

        return $csv;
    }

    /**
     * Parses a CSV file in automatic mode (to detect the delimiter and
     * enclosure), and returns the data rows, including the header row
     * if any.
     *
     * @param string $path
     * @return array
     * @throws CSVHelper_Exception|FileHelper_Exception
     *
     * @see CSVHelper::ERROR_CSV_FILE_NOT_READABLE
     * @see CSVHelper::ERROR_FILE_PARSING_FAILED
     */
    public static function parseFile(string $path) : array
    {
        $path = FileHelper::requireFileReadable($path, self::ERROR_CSV_FILE_NOT_READABLE);

        $parser = self::createParser();
        $result = $parser->parse($path);

        if($result === true) {
            return $parser->data;
        }

        throw new CSVHelper_Exception(
            'Cannot parse CSV file',
            sprintf(
                'The file [%s] could not be parsed. No additional information is available.',
                $path
            ),
            self::ERROR_FILE_PARSING_FAILED
        );
    }

    /**
     * Parses a CSV string in automatic mode (to detect the delimiter and
     * enclosure), and returns the data rows, including the header row
     * if any.
     *
     * @param string $string
     * @return array
     * @throws CSVHelper_Exception
     *
     * @see CSVHelper::ERROR_STRING_PARSING_FAILED
     */
    public static function parseString(string $string) : array
    {
        $parser = self::createParser();
        $result = $parser->parse($string);

        if($result === true) {
            return $parser->data;
        }

        throw new CSVHelper_Exception(
            'Cannot parse CSV string',
            'The string could not be parsed. No additional information is available.',
            self::ERROR_STRING_PARSING_FAILED
        );
    }
}
