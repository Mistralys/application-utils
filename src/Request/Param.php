<?php
/**
 * File containing the {@link Request_Param} class.
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param
 */

namespace AppUtils;

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
class Request_Param
{
    const ERROR_UNKNOWN_VALIDATION_TYPE = 16301;
    
    const ERROR_NOT_A_VALID_CALLBACK = 16302;
    
    const ERROR_INVALID_FILTER_TYPE = 16303;
    
    /**
     * @var Request
     */
    protected $request;

    protected $validationType = 'none';

    protected $paramName;

    protected $validationParams;

    protected $filters = array();

    protected static $validationTypes;

    protected static $filterTypes;

    const VALIDATION_TYPE_NONE = 'none';

    const VALIDATION_TYPE_NUMERIC = 'numeric';

    const VALIDATION_TYPE_INTEGER = 'integer';

    const VALIDATION_TYPE_REGEX = 'regex';

    const VALIDATION_TYPE_ALPHA = 'alpha';

    const VALIDATION_TYPE_ALNUM = 'alnum';
    
    const VALIDATION_TYPE_ENUM = 'enum';

    const VALIDATION_TYPE_ARRAY = 'array';
    
    const VALIDATION_TYPE_CALLBACK = 'callback';

    const VALIDATION_TYPE_URL = 'url';
    
    const VALIDATION_TYPE_VALUESLIST = 'valueslist';
    
    const VALIDATION_TYPE_JSON = 'json';
    
    const FILTER_TYPE_CALLBACK = 'callback';
    
    const FILTER_TYPE_CLASS = 'class';
    
    /**
     * Constructor for the specified parameter name. Note that this
     * is instantiated automatically by the request class itself. You
     * should never have a situation where you instantiate this manually.
     *
     * @param Request $request
     * @param string $paramName
     */
    public function __construct(Request $request, $paramName)
    {
        $this->request = $request;
        $this->paramName = $paramName;

        // initialize the validation types list that is used to
        // make sure that the specified validation types are valid
        // at runtime in case a developer used the wrong one.
        if (!isset(self::$validationTypes)) {
            self::$validationTypes = array(
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
            self::$filterTypes = array(
                self::FILTER_TYPE_CALLBACK,
                self::FILTER_TYPE_CLASS
            );
        }
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
    * @param array $args
    * @return Request_Param
    */
    public function setCallback($callback, array $args=array()) : Request_Param
    {
        if(!is_callable($callback)) {
            throw new Request_Exception(
                'Not a valid callback',
                'The specified callback is not a valid callable entity.',
                self::ERROR_NOT_A_VALID_CALLBACK
            );
        }
        
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
        
        // go through all enqueued validations in turn, each time
        // replacing the value with the adjusted, validated value.
        foreach($this->validations as $validateDef) 
        {
            $value = $this->validateType($value, $validateDef['type'], $validateDef['params']);
        }

        return $value;
    }
    
   /**
    * Validates the specified value using the validation type. Returns
    * the validated value. 
    * 
    * @param mixed $value
    * @param string $type
    * @param array $params
    * @throws Request_Exception
    * @return mixed
    */
    protected function validateType($value, string $type, array $params)
    {
        $class = '\AppUtils\Request_Param_Validator_'.ucfirst($type);
        
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
        
        $validator = new $class($this);
        $validator->setOptions($params);
        
        if($this->valueType === self::VALUE_TYPE_ID_LIST)
        {
            $value = $this->validateType_idList($value, $validator);
        }
        else
        {
            $value = $validator->validate($value);
        }
        
        return $value;
    }
    
    protected function validateType_idList($value, Request_Param_Validator $validator) : array
    {
        if(!is_array($value))
        {
            $value = explode(',', $value);
        }
        
        $keep = array();
        
        foreach($value as $subval)
        {
            $subval = trim($subval);
            $subval = $validator->validate($subval);
            
            if($subval !== null) {
                $keep[] = intval($subval);
            }
        }
         
        return $keep;
    }
    
    /**
     * Sets the parameter value as numeric, meaning it will be validated
     * using PHP's is_numeric method.
     *
     * @return Request_Param
     */
    public function setNumeric()
    {
        return $this->setValidation(self::VALIDATION_TYPE_NUMERIC);
    }

    /**
     * Sets the parameter value as integer, it will be validated using a
     * regex to match only integer values.
     *
     * @return Request_Param
     */
    public function setInteger()
    {
        return $this->setValidation(self::VALIDATION_TYPE_INTEGER);
    }
    
    /**
     * Sets a regex to bu used for validation parameter values.
     * @param string $regex
     * @return Request_Param
     */
    public function setRegex($regex)
    {
        return $this->setValidation(self::VALIDATION_TYPE_REGEX, array('regex' => $regex));
    }
    
    public function setURL()
    {
        return $this->setValidation(self::VALIDATION_TYPE_URL);
    }
    
    const VALUE_TYPE_STRING = 'string';
    
    const VALUE_TYPE_ID_LIST = 'ids_list';
    
    protected $valueType = self::VALUE_TYPE_STRING;

   /**
    * Sets the variable to contain a comma-separated list of integer IDs.
    * Example: <code>145,248,4556</code>. A single ID is also allowed, e.g.
    * <code>145</code>.
    * 
    * @return Request_Param
    */
    public function setIDList()
    {
        $this->valueType = self::VALUE_TYPE_ID_LIST;
        $this->setInteger();
        
        return $this;
    }
    
   /**
    * Sets the variable to be an alias, as defined by the
    * {@link RegexHelper::REGEX_ALIAS} regular expression.
    * 
    * @return Request_Param
    * @see RegexHelper::REGEX_ALIAS
    */
    public function setAlias()
    {
        return $this->setRegex(RegexHelper::REGEX_ALIAS);
    }

    /**
     * Sets the variable to be a name or title, as defined by the
     * {@link RegexHelper::REGEX_NAME_OR_TITLE} regular expression.
     *
     * @return Request_Param
     * @see RegexHelper::REGEX_NAME_OR_TITLE
     */
    public function setNameOrTitle()
    {
        return $this->setRegex(RegexHelper::REGEX_NAME_OR_TITLE);
    }
    
    /**
     * Sets the variable to be a name or title, as defined by the
     * {@link RegexHelper::REGEX_LABEL} regular expression.
     *
     * @return Request_Param
     * @see RegexHelper::REGEX_LABEL
     */
    public function setLabel()
    {
        return $this->setRegex(RegexHelper::REGEX_LABEL);
    }

    /**
     * Sets the parameter value as a string containing only lowercase
     * and/or uppercase letters.
     *
     * @return Request_Param
     */
    public function setAlpha()
    {
        return $this->setValidation(self::VALIDATION_TYPE_ALPHA);
    }
    
   /**
    * Sets the parameter value as a string containing lowercase
    * and/or uppercase letters, as well as numbers.
    * 
    * @return Request_Param
    */
    public function setAlnum()
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
     * It is also possible to specifiy an array of possible values
     * as the first parameter.
     *
     * @return Request_Param
     */
    public function setEnum()
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
    * @param array $values List of allowed values
    * @return \AppUtils\Request_Param
    */
    public function setValuesList(array $values)
    {
        $this->setArray();
        
        return $this->setValidation(
            self::VALIDATION_TYPE_VALUESLIST, 
            array(
                'values' => $values
            )
        );
    }
    
    public function setArray()
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
    * @return \AppUtils\Request_Param
    */
    public function setJSON() : Request_Param
    {
        return $this->setValidation(self::VALIDATION_TYPE_JSON, array('arrays' => true));
    }
    
   /**
    * Like {@link Request_Param::setJSON()}, but accepts
    * only JSON objects. Arrays will not be accepted.
    * 
    * @return \AppUtils\Request_Param
    */
    public function setJSONObject() : Request_Param
    {
        return $this->setValidation(self::VALIDATION_TYPE_JSON, array('arrays' => false));
    }
    
   /**
    * The parameter is a string boolean representation. This means
    * it can be any of the following: "yes", "true", "no", "false".
    * The value is automatically converted to a boolean when retrieving
    * the parameter.
    * 
    * @return Request_Param
    */
    public function setBoolean() : Request_Param
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
    * @return \AppUtils\Request_Param
    */
    public function setMD5() : Request_Param
    {
        return $this->setRegex(RegexHelper::REGEX_MD5);
    }

    protected $validations = array();
    
    /**
     * Sets the validation type to use. See the VALIDATION_TYPE_XX class
     * constants for a list of types to use, or use any of the setXX methods
     * directly as shorthand.
     *
     * @param string $type
     * @param array $params
     * @return Request_Param
     * @throws Request_Exception
     * 
     * @see Request_Param::ERROR_UNKNOWN_VALIDATION_TYPE
     */
    public function setValidation(string $type, array $params = array()) : Request_Param
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

    /**
     * Filters the specified value by going through all available
     * filters, if any. If none have been set, the value is simply
     * passed through.
     *
     * @param mixed $value
     * @return string
     */
    protected function filter($value)
    {
        $total = count($this->filters);
        for ($i = 0; $i < $total; $i++) {
            $method = 'applyFilter_' . $this->filters[$i]['type'];
            $value = $this->$method($value, $this->filters[$i]['params']);
        }

        return $value;
    }
    
    protected function applyFilter_class($value, array $config)
    {
        $class = '\AppUtils\Request_Param_Filter_'.$config['name'];
        
        $filter = new $class($this);
        $filter->setOptions($config['params']);
        
        return $filter->filter($value);
    }

    /**
     * Applies the callback filter.
     * @param mixed $value
     * @param array $callbackDef
     * @return mixed
     */
    protected function applyFilter_callback($value, $callbackDef)
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
     * @return Request_Param
     * @throws Request_Exception
     * 
     * @see Request_Param::ERROR_INVALID_FILTER_TYPE
     */
    public function addFilter($type, $params = null) : Request_Param
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
    * @return \AppUtils\Request_Param
    */
    public function addFilterTrim() : Request_Param
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
    * @return \AppUtils\Request_Param
    */
    public function addStringFilter() : Request_Param
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
     * @param array $params
     * @return Request_Param
     */
    public function addCallbackFilter($callback, $params = array()) : Request_Param
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
     * @return \AppUtils\Request_Param
     */
    public function addStripTagsFilter($allowedTags = '') : Request_Param
    {
        // to ensure we work only with string values.
        $this->addStringFilter();
        
        return $this->addCallbackFilter('strip_tags', array($allowedTags));
    }
    
   /**
    * Adds a filter that strips all whitespace from the
    * request parameter, from spaces to tabs and newlines.
    * 
    * @return \AppUtils\Request_Param
    */
    public function addStripWhitespaceFilter() : Request_Param
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
    * @return \AppUtils\Request_Param
    */
    public function addCommaSeparatedFilter(bool $trimEntries=true, bool $stripEmptyEntries=true) : Request_Param
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
    
    protected function addClassFilter(string $name, array $params=array()) : Request_Param
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
    * @return \AppUtils\Request_Param
    */
    public function addHTMLSpecialcharsFilter() : Request_Param
    {
        return $this->addCallbackFilter('htmlspecialchars', array(ENT_QUOTES, 'UTF-8'));
    }

    public function getName() : string
    {
        return $this->paramName;
    }
    
    protected $required = false;
    
   /**
    * Marks this request parameter as required. To use this feature,
    * you have to call the request's {@link Request::validate()}
    * method.
    * 
    * @return Request_Param
    * @see Request::validate()
    */
    public function makeRequired() : Request_Param
    {
        $this->required = true;
        return $this;
    }
    
    public function isRequired() : bool
    {
        return $this->required;
    }
}
