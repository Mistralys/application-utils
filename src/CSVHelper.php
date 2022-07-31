<?php
/**
 * File containing the {@link CSVHelper} class.
 * 
 * @package Application Utils
 * @subpackage CSVHelper
 * @see CSVHelper
 */

declare(strict_types=1);

namespace AppUtils;

use JsonException;
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

    protected string $csv = '';
    protected string $headersPosition = self::HEADERS_NONE;
    protected string $separator = ';';
    protected int $columnCount = 0;
    protected int $rowCount = 0;

    /**
     * @var string[]
     */
    protected array $errors = array();

    /**
     * @var array<int,array<int,mixed>>
     */
    protected array $data = array();

    /**
     * @var string[]
     */
    protected array $headers = array();

    public function __construct()
    {
        
    }

   /**
    * Creates and returns a new instance of the CSV builder which
    * can be used to build CSV from scratch.
    * 
    * @return CSVHelper_Builder
    */
    public static function createBuilder() : CSVHelper_Builder
    {
        return new CSVHelper_Builder();
    }



   /**
    * Loads CSV data from a string. 
    * 
    * Note: Use the {@link hasErrors()} method to 
    * check if the string could be parsed correctly
    * afterwards.
    * 
    * @param string $string
    * @return $this
    */
    public function loadString(string $string) : self
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
    public function loadFile(string $file) : self
    {
        $csv = FileHelper::readContents($file);
        
        return $this->loadString($csv);
    }

    /**
     * Specifies that headers are positioned on top, horizontally.
     * @return $this
     *
     * @throws CSVHelper_Exception
     */
    public function setHeadersTop() : self
    {
        return $this->setHeadersPosition(self::HEADERS_TOP);
    }

    /**
     * Specifies that headers are positioned on the left, vertically.
     * @return $this
     *
     * @throws CSVHelper_Exception
     */
    public function setHeadersLeft() : self
    {
        return $this->setHeadersPosition(self::HEADERS_LEFT);
    }

    /**
     * Specifies that there are no headers in the file (default).
     * @return $this
     *
     * @throws CSVHelper_Exception
     */
    public function setHeadersNone() : self
    {
        return $this->setHeadersPosition(self::HEADERS_NONE);
    }

    public function isHeadersLeft() : bool
    {
        return $this->isHeadersPosition(self::HEADERS_LEFT);
    }
    
    public function isHeadersTop() : bool
    {
        return $this->isHeadersPosition(self::HEADERS_TOP);
    }
    
    public function isHeadersNone() : bool
    {
        return $this->isHeadersPosition(self::HEADERS_NONE);
    }
    
    public function isHeadersPosition(string $position) : bool
    {
        return $this->headersPosition === $position;
    }
    
   /**
    * Specifies where the headers are positioned in the
    * CSV, or turns them off entirely. Use the class constants
    * to ensure the value is correct.
    * 
    * @param string $position
    * @throws CSVHelper_Exception
    * @return $this
    *
    * @see CSVHelper::HEADERS_LEFT
    * @see CSVHelper::HEADERS_TOP
    * @see CSVHelper::HEADERS_NONE
    */
    public function setHeadersPosition(string $position) : self
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
    * @return $this
    */
    public function reset() : self
    {
        $this->data = array();
        $this->headers = array();
        $this->errors = array();
        $this->columnCount = 0;
        $this->rowCount = 0;
        
        return $this;
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    public function getData() : array
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
    * @return array<int,mixed>
    * @see rowExists()
    */
    public function getRow(int $index) : array
    {
        return $this->data[$index] ?? array_fill(0, $this->rowCount, '');
    }
    
   /**
    * Checks whether the specified row exists in the data set.
    * @param integer $index
    * @return boolean
    */
    public function rowExists(int $index) : bool
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
    public function countRows() : int
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
    public function countColumns() : int
    {
        return $this->columnCount;
    }
    
   /**
    * Retrieves the headers, if any. Specify the position of the
    * headers first to ensure this works correctly.
    * 
    * @return string[] Indexed array with header names.
    */
    public function getHeaders() : array
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
    public function getColumn(int $index) : array
    {
        $data = array();

        for($i=0; $i < $this->rowCount; $i++)
        {
            $value = $this->data[$i][$index] ?? '';

            $data[] = $value;
        }
        
        return $data;
    }
    
   /**
    * Checks whether the specified column exists in the data set.
    * @param integer $index
    * @return boolean
    */
    public function columnExists(int $index) : bool
    {
        return $index < $this->columnCount;
    }
    
    protected function parse() : void
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

                foreach ($result as $value)
                {
                    $row = $value;
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
    public function hasErrors() : bool
    {
        return !empty($this->errors);
    }
    
   /**
    * Retrieves all error messages.
    * @return string[]
    */
    public function getErrorMessages() : array
    {
        return $this->errors;
    }
    
    protected function addError(string $error) : self
    {
        $this->errors[] = $error;
        return $this;
    }
    
    protected function detectSeparator() : string
    {
        $search = array(
            "\"\t\"" => "\t",
            '";"' => ';',
            '","' => ',',
            ';;' => ';',
            ',,' => ','
        );
        
        foreach($search as $char => $separator) {
            if(strpos($this->csv, $char) !== false) {
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
     * @return array<int,array<int,mixed>>
     *
     * @throws CSVHelper_Exception
     * @throws FileHelper_Exception
     * @throws JsonException
     *
     * @see CSVHelper::ERROR_CSV_FILE_NOT_READABLE
     * @see CSVHelper::ERROR_FILE_PARSING_FAILED
     */
    public static function parseFile(string $path) : array
    {
        $path = FileHelper::requireFileReadable($path, self::ERROR_CSV_FILE_NOT_READABLE);

        $parser = self::createParser();
        $parser->heading = false;

        $result = $parser->auto($path);

        if(is_string($result)) {
            return $parser->data;
        }

        throw new CSVHelper_Exception(
            'Cannot parse CSV file',
            sprintf(
                'The file [%s] could not be parsed.'.PHP_EOL.
                'Additional information: '.PHP_EOL.
                '%s',
                $path,
                json_encode($parser->error_info, JSON_THROW_ON_ERROR)
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
     * @return array<int,array<int,mixed>>
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
