<?php
/**
 * Generator script for the LibXML error code definitions used in
 * the XMLHelper_DOMErrors class to identify the type of error.
 * 
 * Usage:
 * 
 * php generate-libxmlerrors.php
 * 
 * @package Application Utils
 * @subpackage XMLHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see http://www.xmlsoft.org/html/libxml-xmlerror.html
 */

   /**
    * @var string $list
    */
    $list = file_get_contents('libxmlerrors.txt');
    $outputFile = 'LibXML.php';
    $lines = explode("\n", $list);
    $reverseArray = array();
    
    foreach($lines as $line)
    {
        $parts = explode('=', $line);
        $name = trim(str_replace('XML_ERR_', '', $parts[0]));
        $number = $parts[1];
        $parts = explode(':', $number);
        $number = trim($parts[0]);
        
        $constants[] = sprintf(
            "    const %s = %s;",
            $name,
            $number
        );
    }
    
    $template = file_get_contents('class.php.tpl');
    
    $php = sprintf($template, implode(PHP_EOL, $constants));
    
    file_put_contents($outputFile, $php);
    
    echo "File [$outputFile] generated.".PHP_EOL;

