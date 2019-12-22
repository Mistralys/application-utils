<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_QueryParser} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_QueryParser
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Query parser that works as a drop-in for the native
 * PHP function parse_str, and which overcomes this function's
 * limitations.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see https://www.php.net/manual/en/function.parse-str.php
 */
class ConvertHelper_QueryParser
{
    public function __construct()
    {
        
    }
    
    public function parse(string $queryString) : array
    {
        // allow HTML entities notation
        $queryString = str_replace('&amp;', '&', $queryString);
        
        $paramNames = array();
        
        // extract parameter names from the query string
        $result = array();
        preg_match_all('/&?([^&]+)=.*/sixU', $queryString, $result, PREG_PATTERN_ORDER);
        if(isset($result[1])) {
            $paramNames = $result[1];
        }
        
        // to avoid iterating over the param names, we simply concatenate it
        $search = implode('', $paramNames);
        
        // store whether we need to adjust any of the names:
        // this is true if we find dots or spaces in any of them.
        $fixRequired = stristr($search, '.') || stristr($search, ' ');
        
        unset($search);
        
        $table = array();
        
        // A fix is required: replace all parameter names with placeholders,
        // which do not conflict with parse_str and which will be restored
        // with the actual parameter names after the parsing.
        //
        // It is necessary to do this even before the parsing, to resolve
        // possible naming conflicts like having both parameters "foo.bar"
        // and "foo_bar" in the query string: since "foo.bar" would be converted
        // to "foo_bar", one of the two would be replaced.
        if($fixRequired)
        {
            $counter = 1;
            $placeholders = array();
            foreach($paramNames as $paramName)
            {
                // create a unique placeholder name
                $placeholder = '__PLACEHOLDER'.$counter.'__';
                
                // store the placeholder name to replace later
                $table[$placeholder] = $paramName;
                
                // add the placeholder to replace in the query string before parsing
                $placeholders[$paramName.'='] = $placeholder.'=';
                
                $counter++;
            }
            
            // next challenge: replacing the parameter names by placeholders
            // safely. We sort the list by longest name first, to avoid shorter
            // parameter names being replaced first that can be part of longer ones.
            uksort($placeholders, function($a, $b) {
                return strlen($b) - strlen($a);
            });
                
            // replace all instances with the placeholder
            $queryString = str_replace(array_keys($placeholders), array_values($placeholders), $queryString);
        }
        
        // parse the query string natively
        $parsed = array();
        parse_str($queryString, $parsed);
        
        // do any of the parameter names need to be fixed?
        if(!$fixRequired) {
            return $parsed;
        }
        
        $keep = array();
        
        foreach($parsed as $name => $value)
        {
            $keep[$table[$name]] = $value;
        }
        
        return $keep;
    }
}
