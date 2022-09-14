<?php
/**
 * File containing the {@see AppUtils\Highlighter} class.
 *
 * @package Application Utils
 * @subpackage Highlighter
 * @see AppUtils\Highlighter
 */

namespace AppUtils;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use GeSHi;
use DOMDocument;

/**
 * Syntax highlighter helper: Uses GeSHi and other ways to add syntax
 * highlighting to a range of formats. Adds some GeSHi factory methods.
 *
 * Usage:
 * 
 * Parsing source code from a string or file
 * 
 * <pre>
 * $highlighted = Highlighter::fromString($xml, 'xml');
 * $highlighted = Highlighter::fromFile('/path/to/file.xml', 'xml');
 * </pre>
 * 
 * Creating a GeSHi instance from a string or file
 * 
 * <pre>
 * $geshi = Highlighter::fromString($xml, 'xml');
 * $geshi = Highlighter::fromFile('/path/to/file.xml', 'xml');
 * </pre>
 * 
 * Other, more specialized formats are available in the
 * according format methods, e.g. <code>json()</code>, <code>url()</code>.
 *
 * @package Application Utils
 * @subpackage Highlighter
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Highlighter
{
   /**
    * Creates a new GeSHi instance from a source code string.
    * 
    * @param string $sourceCode
    * @param string $format
    * @return GeSHi
    */
    public static function fromString(string $sourceCode, string $format) : GeSHi
    {
        return new GeSHi($sourceCode, $format);
    }
    
   /**
    * Creates a new GeSHi instance from the contents of a file.
    * 
    * @param string $path
    * @param string $format
    * @return GeSHi
    */
    public static function fromFile(string $path, string $format) : GeSHi
    {
        return self::fromString(FileHelper::readContents($path), $format);
    }
    
   /**
    * Parses and highlights the target string.
    * 
    * @param string $sourceCode
    * @param string $format
    * @return string
    */
    public static function parseString(string $sourceCode, string $format) : string
    {
        return self::fromString($sourceCode, $format)->parse_code();
    }
    
   /**
    * Parses and highlights the contents of the target file.
    * 
    * @param string $path
    * @param string $format
    * @return string
    */
    public static function parseFile(string $path, string $format) : string
    {
        return self::fromFile($path, $format)->parse_code();
    }
    
   /**
    * Adds HTML syntax highlighting to the specified SQL string.
    *
    * @param string $sql
    * @return string
    */
    public static function sql(string $sql) : string
    {
        return self::parseString($sql, 'sql');
    }

    /**
     * Adds HTML syntax highlighting to a JSON string, or a data array/object.
     *
     * @param array<int|string,mixed>|object|string $subject A JSON string, or data array/object to convert to JSON to highlight.
     * @return string
     *
     * @throws JSONConverterException
     */
    public static function json($subject) : string
    {
        if(!is_string($subject))
        {
            $subject = JSONConverter::var2json($subject, JSON_PRETTY_PRINT);
        }
        
        $subject = str_replace('\/', '/', $subject);
        
        return self::parseString($subject, 'javascript');
    }
    
   /**
    * Adds HTML syntax highlighting to the specified XML code.
    *
    * @param string $xml The XML to highlight.
    * @param bool $formatSource Whether to format the source with indentation to make it readable.
    * @return string
    */
    public static function xml(string $xml, bool $formatSource=false) : string
    {
        if($formatSource)
        {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            $dom->loadXML($xml);
            
            $xml = $dom->saveXML();
        }
        
        return self::parseString($xml, 'xml');
    }
    
   /**
    * Adds HTML syntax highlighting to the specified HTML code.
    * 
    * @param string $html
    * @param bool $formatSource
    * @return string
    */
    public static function html(string $html, bool $formatSource=false) : string
    {
        if($formatSource)
        {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            $dom->loadHTML($html);
            
            $html = $dom->saveHTML();
        }
        
        return self::parseString($html, 'xml');
    }
    
   /**
    * Adds HTML syntax highlighting to a bit of PHP code.
    * 
    * @param string $phpCode
    * @return string
    */
    public static function php(string $phpCode) : string
    {
        return self::parseString($phpCode, 'php');
    }
    
   /**
    * Adds HTML syntax highlighting to an URL.
    *
    * NOTE: Includes the necessary CSS styles. When
    * highlighting several URLs in the same page,
    * prefer using the `parseURL` function instead.
    *
    * @param string $url
    * @return string
    */
    public static function url(string $url) : string
    {
        $info = parseURL($url);
        
        return
        '<style>'.$info->getHighlightCSS().'</style>'.
        $info->getHighlighted();
    }
}
