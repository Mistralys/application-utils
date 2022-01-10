<?php
/**
 * File containing the {@link JSHelper} class.
 * 
 * @package Application Utils
 * @subpackage JSHelper
 * @see JSHelper
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Simplifies building JavaScript statements from PHP variables.
 * Automatically converts variables to their JS equivalents, 
 * with different quote styles for usage within scripts or HTML
 * tag attributes.
 * 
 * Also offers a way to easily generate unique element IDs within
 * a single request.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class JSHelper
{
   /**
    * Quote style using single quotes.
    * @var integer
    */
    public const QUOTE_STYLE_SINGLE = 1;
    
   /**
    * Quote style using double quotes.
    * @var integer
    */
    public const QUOTE_STYLE_DOUBLE = 2;

   /**
    * @var array
    */
    protected static $variableCache = array();
    
   /**
    * @var integer
    */
    protected static $elementCounter = 0;

   /**
    * @var string
    */    
    protected static $idPrefix = 'E';
    
   /**
    * Builds a javascript statement. The first parameter is the
    * javascript function to call, any additional parameters are
    * used as arguments for the javascript function call. Variable
    * types are automagically converted to the javascript equivalents.
    *
    * Examples:
    *
    * // add an alert(); statement:
    * JSHelper::buildStatement('alert');
    *
    * // add an alert('Alert text'); statement
    * JSHelper::buildStatement('alert', 'Alert text');
    *
    */
    public static function buildStatement() : string
    {
        $args = func_get_args();
        array_unshift($args, self::QUOTE_STYLE_DOUBLE);
        return call_user_func_array(array(self::class, 'buildStatementQuoteStyle'), $args);
    }
    
   /**
    * Like {@link JSHelper::buildStatement()}, but using single quotes
    * to make it possible to use the statement in an HTML tag attribute.
    * 
    * @return string
    * @see JSHelper::buildStatement()
    */
    public static function buildStatementAttribute() : string
    {
        $args = func_get_args();
        array_unshift($args, self::QUOTE_STYLE_SINGLE);
        return call_user_func_array(array(self::class, 'buildStatementQuoteStyle'), $args);
    }
    
    protected static function buildStatementQuoteStyle()
    {
        $params = func_get_args();
        $quoteStyle = array_shift($params);
        $method = array_shift($params);
        
        $call = $method . '(';
        
        $total = count($params);
        if($total > 0) {
            for($i=0; $i < $total; $i++) 
            {
                $call .= self::phpVariable2JS($params[$i], $quoteStyle);
                if($i < ($total-1)) {
                    $call .= ',';
                }
            }
        }
        
        return $call . ');';
    }

   /**
    * Builds a set variable statement. The variable value is
    * automatically converted to the javascript equivalent.
    *
    * Examples:
    *
    * // foo = 'bar';
    * JSHelper::buildVariable('foo', 'bar');
    *
    * // foo = 42;
    * JSHelper::buildVariable('foo', 42);
    *
    * // foo = true;
    * JSHelper::buildVariable('foo', true);
    *
    * @param string $varName
    * @param mixed $varValue
    * @return string
    */
    public static function buildVariable(string $varName, $varValue) : string
    {
        return $varName . "=" . self::phpVariable2JS($varValue) . ';';
    }
    
   /**
    * Converts a PHP variable to its javascript equivalent. Note that
    * if a variable cannot be converted (like a PHP resource), this will
    * return a javascript "null".
    *
    * @param mixed $variable
    * @param int $quoteStyle The quote style to use for strings
    * @return string
    */
    public static function phpVariable2JS($variable, int $quoteStyle=self::QUOTE_STYLE_DOUBLE) : string
    {
        // after much profiling, this variant of the method offers
        // the best performance. Repeat scalar values are cached 
        // internally, others are likely not worth caching.
        
        $type = gettype($variable);
        $hash = null;
        if(is_scalar($variable) === true) 
        {
            $hash = $variable;
        
            if($hash === true) 
            { 
                $hash = 'true'; 
            } 
            else if($hash === false) 
            { 
                $hash = 'false'; 
            }
            
            $hash .= '-'.$quoteStyle.'-'.$type;
            
            if(isset(self::$variableCache[$hash])) {
                return self::$variableCache[$hash];
            }
        }
            
        $result = 'null';

        // one gettype call is better than a strict if-else.
        switch($type) 
        {
            case 'double':
            case 'string':
                $string = json_encode($variable);
                
                if($string === false) 
                {
                    $string = '';
                } 
                else if($quoteStyle === self::QUOTE_STYLE_SINGLE) 
                {
                    $string = mb_substr($string, 1, mb_strlen($string)-2);
                    $string = "'".str_replace("'", "\'", $string)."'";
                }
                
                $result = $string;
                break;
                
            case 'boolean':
                if($variable === true) {
                    $result = 'true';
                } else {
                    $result = 'false';
                }
                break;

            case 'integer':
                $result = (string)$variable;
                break;

            case 'object':
            case 'array':
                $result = json_encode($variable);
                break;
        }

        // cache cacheable values
        if($hash !== null) 
        {
            self::$variableCache[$hash] = $result;
        }

        return $result;
    }
    
   /**
    * Converts a variable to a JS string that can be 
    * used in an HTML attribute: it uses single quotes
    * instead of the default double quotes.
    * 
    * @param mixed $variable
    * @return string
    */
    public static function phpVariable2AttributeJS($variable) : string
    {
        return self::phpVariable2JS($variable, self::QUOTE_STYLE_SINGLE);
    }

   /**
    * Generates a dynamic element ID to be used with dynamically generated
    * HTML code to tie in with clientside javascript when compact but unique
    * IDs are needed in a  request.
    *
    * @return string
    */
    public static function nextElementID() : string
    {
        self::$elementCounter++;

        return self::$idPrefix . self::$elementCounter;
    }
    
   /**
    * Retrieves the ID prefix currently used.
    * 
    * @return string
    */
    public static function getIDPrefix() : string
    {
        return self::$idPrefix;
    }
    
   /**
    * Retrieves the value of the internal elements counter.
    * 
    * @return integer
    */
    public static function getElementCounter() : int
    {
        return self::$elementCounter;
    }
    
   /**
    * Sets the prefix that is added in front of all IDs
    * retrieved using the {@link nextElementID()} method.
    * 
    * @param string $prefix
    * @see JSHelper::nextElementID()
    */
    public static function setIDPrefix(string $prefix)
    {
        self::$idPrefix = $prefix;
    }

    public const JS_REGEX_OBJECT = 'object';
    
    public const JS_REGEX_JSON = 'json';
    
    /**
     * Takes a regular expression and attempts to convert it to
     * its javascript equivalent. Returns an array containing the
     * format string itself (without start and end characters),
     * and the modifiers.
     *
     * By default, the method returns a javascript statement
     * to create a RegExp object:
     *
     * ```php
     * <script>
     * var reg = <?php echo ConvertHelper::regex2js('/ab+c/i') ?>;
     * </script>
     * ```
     *
     * The second way allows accessing the format and the modifiers
     * separately, by storing these in a variable first:
     *
     * ```php
     * <script>
     * // define the regex details
     * var expression = <?php echo json_encode(ConvertHelper::regex2js('/ab+c/i', ConvertHelper::JS_REGEX_JSON)) ?>;
     *
     * // create the regex object
     * var reg = new RegExp(expression.format, expression.modifiers);
     * </script>
     * ```
     *
     * @param string $regex A PHP preg regex
     * @param string $statementType The statement type to generate: Default to a statement to create a RegExp object.
     * @return string
     *
     * @see JSHelper::JS_REGEX_OBJECT
     * @see JSHelper::JS_REGEX_JSON
     */
    public static function buildRegexStatement(string $regex, string $statementType=self::JS_REGEX_OBJECT) : string
    {
        $regex = trim($regex);
        $separator = substr($regex, 0, 1);
        $parts = explode($separator, $regex);
        array_shift($parts);
        
        $modifiers = array_pop($parts);
        if($modifiers == $separator) {
            $modifiers = '';
        }
        
        $modifierReplacements = array(
            's' => '',
            'U' => ''
        );
        
        $modifiers = str_replace(array_keys($modifierReplacements), array_values($modifierReplacements), $modifiers);
        
        $format = implode($separator, $parts);
        
        // convert the anchors that are not supported in js regexes
        $format = str_replace(array('\\A', '\\Z', '\\z'), array('^', '$', ''), $format);
        
        if($statementType==self::JS_REGEX_JSON)
        {
            return ConvertHelper::var2json(array(
                'format' => $format,
                'modifiers' => $modifiers
            ));
        }
        
        if(!empty($modifiers)) {
            return sprintf(
                'new RegExp(%s, %s)',
                ConvertHelper::var2json($format),
                ConvertHelper::var2json($modifiers)
            );
        }
        
        return sprintf(
            'new RegExp(%s)',
            ConvertHelper::var2json($format)
        );
    }
}
