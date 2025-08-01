<?php
/**
 * File containing the {@link \AppUtils\Request\RequestParam} class.
 * @package Application Utils
 * @subpackage Request
 * @see \AppUtils\Request\RequestParam
 */

declare(strict_types=1);

namespace AppUtils\Request;

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\RegexHelper;
use AppUtils\Request;
use AppUtils\Request_Exception;
use AppUtils\Request_Param_Filter;
use AppUtils\Request_Param_Validator;

/**
 * Class used for handling a single request parameter - implements
 * validation and filtering. It is possible to filter values or
 * validate them, or both. Filtering is done before validation.
 *
 * Usage: use any of the setXX() methods to set the validation
 * type to use (only one validation type may be used per parameter),
 * and any of the addXXFilter() methods to specific filters to use.
 * Filters can be stacked, and are applied in the order that they
 * are added.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestParam
{
    public const ERROR_UNKNOWN_VALIDATION_TYPE = 16301;
    public const ERROR_INVALID_FILTER_TYPE = 16303;
    public const ERROR_INVALID_FILTER_CLASS = 16304;

    public const VALIDATION_TYPE_NONE = 'none';
    public const VALIDATION_TYPE_NUMERIC = 'numeric';
    public const VALIDATION_TYPE_INTEGER = 'integer';
    public const VALIDATION_TYPE_REGEX = 'regex';
    public const VALIDATION_TYPE_ALPHA = 'alpha';
    public const VALIDATION_TYPE_ALNUM = 'alnum';
    public const VALIDATION_TYPE_ENUM = 'enum';
    public const VALIDATION_TYPE_ARRAY = 'array';
    public const VALIDATION_TYPE_CALLBACK = 'callback';
    public const VALIDATION_TYPE_URL = 'url';
    public const VALIDATION_TYPE_VALUESLIST = 'valueslist';
    public const VALIDATION_TYPE_JSON = 'json';

    public const FILTER_TYPE_CALLBACK = 'callback';
    public const FILTER_TYPE_CLASS = 'class';

    public const VALUE_TYPE_STRING = 'string';
    public const VALUE_TYPE_LIST = 'ids_list';

    protected Request $request;
    protected string $paramName;
    protected bool $required = false;
    protected string $valueType = self::VALUE_TYPE_STRING;

    /**
     * @var array<int,array{type:string,params:mixed}>
     */
    protected array $filters = array();

    /**
     * @var string[]
     */
    protected static array $validationTypes = array(
        self::VALIDATION_TYPE_NONE,
        self::VALIDATION_TYPE_ALPHA,
        self::VALIDATION_TYPE_ALNUM,
        self::VALIDATION_TYPE_ENUM,
        self::VALIDATION_TYPE_INTEGER,
        self::VALIDATION_TYPE_NUMERIC,
        self::VALIDATION_TYPE_REGEX,
        self::VALIDATION_TYPE_ARRAY,
        self::VALIDATION_TYPE_CALLBACK,
        self::VALIDATION_TYPE_URL,
        self::VALIDATION_TYPE_VALUESLIST,
        self::VALIDATION_TYPE_JSON
    );

    /**
     * @var string[]
     */
    protected static array $filterTypes = array(
        self::FILTER_TYPE_CALLBACK,
        self::FILTER_TYPE_CLASS
    );

    /**
     * @var array<int,array{type:string,params:array<string,mixed>}>
     */
    protected array $validations = array();

    /**
     * Constructor for the specified parameter name. Note that this
     * is instantiated automatically by the request class itself. You
     * should never have a situation where you instantiate this manually.
     *
     * @param Request $request
     * @param string $paramName
     */
    public function __construct(Request $request, string $paramName)
    {
        $this->request = $request;
        $this->paramName = $paramName;
    }

    /**
     * Adds a callback as a validation method. The callback gets the
     * value to validate as first parameter, and any additional
     * parameters passed here get appended to that.
     *
     * The callback must return boolean true or false depending on
     * whether the value is valid.
     *
     * @param callable $callback
     * @param array<mixed> $args
     * @return $this
     * @throws Request_Exception
     */
    public function setCallback(callable $callback, array $args=array()) : self
    {
        return $this->setValidation(
            self::VALIDATION_TYPE_CALLBACK, 
            array(
                'callback' => $callback,
                'arguments' => $args
            )
        );
    }
    
    /**
     * Validates a request parameter: called automatically for all
     * registered parameters by the request class. If no specific
     * parameter type has been selected, the value will simply be
     * passed through.
     *
     * @param mixed $value
     * @return mixed
     */
    public function validate($value)
    {
        // first off, apply filtering
        $value = $this->filter($value);
        
        if($this->valueType === self::VALUE_TYPE_LIST)
        {
            if(!is_array($value))
            {
                $value = explode(',', $value);
            }
            
            $keep = array();
            
            foreach($value as $subval)
            {
                $subval = $this->filter($subval);
                
                $subval = $this->applyValidations($subval, true);

                if($subval !== null) {
                    $keep[] = $subval;
                }
            }
            
            return $keep;
        }
        
        $value = $this->filter($value);
        
        return $this->applyValidations($value);
    }
    
   /**
    * Runs the value through all validations that were added.
    * 
    * @param mixed $value
    * @return mixed
    */
    protected function applyValidations($value, bool $subval=false)
    {
        // go through all enqueued validations in turn, each time
        // replacing the value with the adjusted, validated value.
        foreach($this->validations as $validateDef)
        {
            $value = $this->validateType($value, $validateDef['type'], $validateDef['params'], $subval);
        }
        
        return $value;
    }
    
   /**
    * Validates the specified value using the validation type. Returns
    * the validated value. 
    * 
    * @param mixed $value
    * @param string $type
    * @param array<string,mixed> $params
    * @param bool $subval Whether this is a subvalue in a list
    * @throws Request_Exception
    * @return mixed
    */
    protected function validateType($value, string $type, array $params, bool $subval)
    {
        $class = Request_Param_Validator::class.'_'.ucfirst($type);
        
        if(!class_exists($class))
        {
            throw new Request_Exception(
                'Unknown validation type.',
                sprintf(
                    'Cannot validate using type [%s], the target class [%s] does not exist.',
                    $type,
                    $class
                ),
                self::ERROR_UNKNOWN_VALIDATION_TYPE
            );
        }
        
        $validator = new $class($this, $subval);
        $validator->setOptions($params);
        
        return $validator->validate($value);
    }
    
    /**
     * Sets the parameter value as numeric, meaning it will be validated
     * using PHP's is_numeric method.
     *
     * @return $this
     */
    public function setNumeric() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_NUMERIC);
    }

    /**
     * Sets the parameter value as integer, it will be validated using a
     * regex to match only integer values.
     *
     * @return $this
     */
    public function setInteger() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_INTEGER);
    }
    
    /**
     * Sets a regex to bu used for validation parameter values.
     * @param string $regex
     * @return $this
     */
    public function setRegex(string $regex) : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_REGEX, array('regex' => $regex));
    }

    /**
     * @return $this
     * @throws Request_Exception
     */
    public function setURL() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_URL);
    }
    
   /**
    * Sets the variable to contain a comma-separated list of integer IDs.
    * Example: <code>145,248,4556</code>. A single ID is also allowed, e.g.
    * <code>145</code>.
    * 
    * @return $this
    */
    public function setIDList() : self
    {
        $this->valueType = self::VALUE_TYPE_LIST;
        $this->addFilterTrim();
        $this->setInteger();
        
        return $this;
    }
    
   /**
    * Sets the variable to be an alias, as defined by the
    * {@link RegexHelper::REGEX_ALIAS} regular expression.
    * 
    * @return $this
    * @see RegexHelper::REGEX_ALIAS
    */
    public function setAlias() : self
    {
        return $this->setRegex(RegexHelper::REGEX_ALIAS);
    }

    /**
     * Sets the variable to be a name or title, as defined by the
     * {@link RegexHelper::REGEX_NAME_OR_TITLE} regular expression.
     *
     * @return $this
     * @see RegexHelper::REGEX_NAME_OR_TITLE
     */
    public function setNameOrTitle() : self
    {
        return $this->setRegex(RegexHelper::REGEX_NAME_OR_TITLE);
    }
    
    /**
     * Sets the variable to be a name or title, as defined by the
     * {@link RegexHelper::REGEX_LABEL} regular expression.
     *
     * @return $this
     * @see RegexHelper::REGEX_LABEL
     */
    public function setLabel() : self
    {
        return $this->setRegex(RegexHelper::REGEX_LABEL);
    }

    /**
     * Sets the parameter value as a string containing only lowercase
     * and/or uppercase letters.
     *
     * @return $this
     */
    public function setAlpha() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_ALPHA);
    }
    
   /**
    * Sets the parameter value as a string containing lowercase
    * and/or uppercase letters, as well as numbers.
    * 
    * @return $this
    */
    public function setAlnum() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_ALNUM);   
    }

    /**
     * Validates that the parameter value is one of the specified values.
     *
     * Note: specify possible values as parameters to this function.
     * If you do not specify any values, the validation will always
     * fail.
     *
     * It is also possible to specify an array of possible values
     * as the first parameter.
     *
     * @return $this
     * @throws Request_Exception
     */
    public function setEnum() : self
    {
        $args = func_get_args(); // cannot be used as function parameter in some PHP versions
        
        if(is_array($args[0])) 
        {
            $args = $args[0];
        }

        return $this->setValidation(
            self::VALIDATION_TYPE_ENUM, 
            array('values' => $args)
        );
    }

    /**
     * Only available for array values: the parameter must be
     * an array value, and the array may only contain values
     * specified in the values array.
     *
     * Submitted values that are not in the allowed list of
     * values are stripped from the value.
     *
     * @param array<int,string|number> $values List of allowed values
     * @return $this
     * @throws Request_Exception
     */
    public function setValuesList(array $values) : self
    {
        $this->setArray();
        
        return $this->setValidation(
            self::VALIDATION_TYPE_VALUESLIST, 
            array(
                'values' => $values
            )
        );
    }
    
   /**
    * Whether the parameter is a list of values.
    * 
    * @return bool
    */
    public function isList() : bool
    {
        return $this->valueType === self::VALUE_TYPE_LIST;
    }

    /**
     * @return $this
     * @throws Request_Exception
     */
    public function setArray() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_ARRAY);
    }

    /**
     * Specifies that a JSON-encoded string is expected.
     *
     * NOTE: Numbers or quoted strings are technically valid
     * JSON, but are not accepted, because it is assumed
     * at least an array or object are expected.
     *
     * @return $this
     * @throws Request_Exception
     */
    public function setJSON() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_JSON, array('arrays' => true));
    }

    /**
     * Like {@link RequestParam::setJSON()}, but accepts
     * only JSON objects. Arrays will not be accepted.
     *
     * @return $this
     * @throws Request_Exception
     */
    public function setJSONObject() : self
    {
        return $this->setValidation(self::VALIDATION_TYPE_JSON, array('arrays' => false));
    }
    
   /**
    * The parameter is a string boolean representation. This means
    * it can be any of the following: "yes", "true", "no", "false".
    * The value is automatically converted to a boolean when retrieving
    * the parameter.
    * 
    * @return $this
    */
    public function setBoolean() : self
    {
        return $this->addClassFilter('Boolean');
    }
    
   /**
    * Validates the request parameter as an MD5 string,
    * so that only values resembling md5 values are accepted.
    * 
    * NOTE: This can only guarantee the format, not whether
    * it is an actual valid hash of something.
    * 
    * @return $this
    */
    public function setMD5() : self
    {
        return $this->setRegex(RegexHelper::REGEX_MD5);
    }

    /**
     * Sets the validation type to use. See the VALIDATION_TYPE_XX class
     * constants for a list of types to use, or use any of the setXX methods
     * directly as shorthand.
     *
     * @param string $type
     * @param array<string,mixed> $params
     * @return $this
     * @throws Request_Exception
     * 
     * @see RequestParam::ERROR_UNKNOWN_VALIDATION_TYPE
     */
    public function setValidation(string $type, array $params = array()) : self
    {
        if (!in_array($type, self::$validationTypes)) {
            throw new Request_Exception(
                'Invalid validation type',
                sprintf(
                    'Tried setting the validation type to "%1$s". Possible validation types are: %2$s. Use the class constants VALIDATION_TYPE_XXX to set the desired validation type to avoid errors like this.',
                    $type,
                    implode(', ', self::$validationTypes)
                ),
                self::ERROR_UNKNOWN_VALIDATION_TYPE
            );
        }

        $this->validations[] = array(
            'type' => $type,
            'params' => $params
        );

        return $this;
    }

    // region: Getting the value

   /**
    * Retrieves the value of the request parameter,
    * applying all filters (if any) and validation
    * (if any).
    * 
    * @param mixed $default
    * @return mixed
    */
    public function get($default=null)
    {
        $value = $this->request->getParam($this->paramName);
        if($value !== null && $value !== '') {
            return $value;
        }

        return $this->validate($default);
    }

    public function getInt(int $default=0) : int
    {
        return (int)$this->get($default);
    }

    public function getString(string $default='') : string
    {
        return (string)$this->get($default);
    }

    /**
     * @param bool $default
     * @return bool
     * @throws ConvertHelper_Exception {@see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING}
     */
    public function getBool(bool $default=false) : bool
    {
        return ConvertHelper::string2bool($this->get($default));
    }

    public function getFloat(float $default=0.0) : float
    {
        return (float)$this->get($default);
    }

    // endregion

    // region: Filtering

   /**
    * Filters the specified value by going through all available
    * filters, if any. If none have been set, the value is simply
    * passed through.
    *
    * @param mixed $value
    * @return mixed
    *
    * @see RequestParam::applyFilter_callback()
    * @see RequestParam::applyFilter_class()
    */
    protected function filter($value)
    {
        foreach ($this->filters as $filter)
        {
            $method = 'applyFilter_' . $filter['type'];
            $value = $this->$method($value, $filter['params']);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param array<string,mixed> $config
     * @return mixed
     * @throws Request_Exception
     */
    protected function applyFilter_class($value, array $config)
    {
        $class = Request_Param_Filter::class.'_'.$config['name'];
        
        $filter = new $class($this);

        if($filter instanceof Request_Param_Filter)
        {
            $filter->setOptions($config['params']);
            return $filter->filter($value);
        }
        
        throw new Request_Exception(
            'Not a valid filter class',
            sprintf(
                'The class [%s] does not extend [%s].',
                $class,
                Request_Param_Filter::class
            ),
            self::ERROR_INVALID_FILTER_CLASS
        );
    }

    /**
     * Applies the callback filter.
     * @param mixed $value
     * @param array<mixed> $callbackDef
     * @return mixed
     */
    protected function applyFilter_callback($value, array $callbackDef)
    {
        $params = $callbackDef['params'];
        array_unshift($params, $value);

        return call_user_func_array($callbackDef['callback'], $params);
    }

    /**
     * Adds a filter to apply to the parameter value before validation.
     * See the FILTER_XX class constants for available types, or use any
     * of the addXXFilter methods as shorthand.
     *
     * @param string $type
     * @param mixed $params
     * @return $this
     * @throws Request_Exception
     * 
     * @see RequestParam::ERROR_INVALID_FILTER_TYPE
     */
    public function addFilter(string $type, $params = null) : self
    {
        if (!in_array($type, self::$filterTypes)) {
            throw new Request_Exception(
                'Invalid filter type',
                sprintf(
                    'Tried setting the filter type to "%1$s". Possible validation types are: %2$s. Use the class constants FILTER_XXX to set the desired validation type to avoid errors like this.',
                    $type,
                    implode(', ', self::$filterTypes)
                ),
                self::ERROR_INVALID_FILTER_TYPE
            );
        }

        $this->filters[] = array(
            'type' => $type,
            'params' => $params
        );

        return $this;
    }
    
   /**
    * Adds a filter that trims whitespace from the request
    * parameter using the PHP <code>trim</code> function.
    * 
    * @return $this
    */
    public function addFilterTrim() : self
    {
        // to guarantee we only work with strings
        $this->addStringFilter();
        
        return $this->addCallbackFilter('trim');
    }

   /**
    * Converts the value to a string, even if it is not
    * a string value. Complex types like arrays and objects
    * are converted to an empty string.
    * 
    * @return $this
    */
    public function addStringFilter() : self
    {
        return $this->addClassFilter('String');
    }

    /**
     * Adds a filter using the specified callback. Can be any
     * type of callback, for example:
     *
     * // use the trim() function on the value
     * addCallbackFilter('trim');
     *
     * // use an object's method
     * addCallbackFilter(array($object, 'methodName'));
     *
     * // specify additional callback function parameters using an array (first one is always the value)
     * addCallbackFilter('strip_tags', array('<b><a><ul>'));
     *
     * @param mixed $callback
     * @param array<mixed> $params
     * @return $this
     *
     * @throws Request_Exception
     */
    public function addCallbackFilter($callback, array $params = array()) : self
    {
        return $this->addFilter(
            self::FILTER_TYPE_CALLBACK,
            array(
                'callback' => $callback,
                'params' => $params
            )
        );
    }

    /**
     * Adds a strip tags filter to the stack using PHP's strip_tags
     * function. Specify allowed tags like you would for the function
     * like this: "<b><a><ul>", or leave it empty for none.
     *
     * @param string $allowedTags
     * @return $this
     */
    public function addStripTagsFilter(string $allowedTags = '') : self
    {
        // to ensure we work only with string values.
        $this->addStringFilter();
        
        return $this->addCallbackFilter('strip_tags', array($allowedTags));
    }
    
   /**
    * Adds a filter that strips all whitespace from the
    * request parameter, from spaces to tabs and newlines.
    * 
    * @return $this
    */
    public function addStripWhitespaceFilter() : self
    {
        // to ensure we only work with strings.
        $this->addStringFilter();
        
        return $this->addClassFilter('StripWhitespace');
    }   
    
   /**
    * Adds a filter that transforms comma separated values
    * into an array of values.
    * 
    * @param bool $trimEntries Trim whitespace from each entry?
    * @param bool $stripEmptyEntries Remove empty entries from the array?
    * @return $this
    */
    public function addCommaSeparatedFilter(bool $trimEntries=true, bool $stripEmptyEntries=true) : self
    {
        $this->setArray();
        
        return $this->addClassFilter(
            'CommaSeparated', 
            array(
                'trimEntries' => $trimEntries,
                'stripEmptyEntries' => $stripEmptyEntries
            )
        );
    }

    /**
     * @param string $name
     * @param array<string,mixed> $params
     * @return $this
     * @throws Request_Exception
     */
    protected function addClassFilter(string $name, array $params=array()) : self
    {
        return $this->addFilter(
            self::FILTER_TYPE_CLASS,
            array(
                'name' => $name,
                'params' => $params
            )
        );
    }

   /**
    * Adds a filter that encodes all HTML special characters
    * using the PHP <code>htmlspecialchars</code> function.
    * 
    * @return $this
    */
    public function addHTMLSpecialcharsFilter() : self
    {
        return $this->addCallbackFilter(
            function($value) : string {
                if(is_numeric($value) || is_string($value)) {
                    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8', false);
                }

                return '';
            }
        );
    }

    // endregion

    public function getName() : string
    {
        return $this->paramName;
    }
    
   /**
    * Marks this request parameter as required. To use this feature,
    * you have to call the request's {@link Request::validate()}
    * method.
    * 
    * @return RequestParam
    * @see Request::validate()
    */
    public function makeRequired() : RequestParam
    {
        $this->required = true;
        return $this;
    }
    
    public function isRequired() : bool
    {
        return $this->required;
    }
}
