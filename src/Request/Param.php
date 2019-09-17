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
    
    const FILTER_TYPE_CALLBACK = 'callback';

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
                self::VALIDATION_TYPE_URL
            );
            self::$filterTypes = array(
                self::FILTER_TYPE_CALLBACK
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
    * @param mixed $callback
    * @param array $args
    * @return Request_Param
    */
    public function setCallback($callback, $args=array())
    {
        if(!is_callable($callback)) {
            throw new Application_Exception(
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
    
    protected function validate_callback($value)
    {
        $args = $this->validationParams['arguments'];
        array_unshift($args, $value);
        
        $result = call_user_func_array($this->validationParams['callback'], $args);
        if(call_user_func_array($this->validationParams['callback'], $args) !== false) {
            return $value;
        }
        
        return null;
    }
    
    protected $validated = false;
    
    protected $validatedValue = null;

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
        if($this->validated) {
            return $this->validatedValue;
        }
        
        // first off, apply filtering
        $value = $this->filter($value);

        // go through all enqueued validations in turn, each time
        // replacing the value with the adjusted, validated value.
        foreach($this->validations as $validateDef) 
        {
            $this->validationType = $validateDef['type'];
            $this->validationParams = $validateDef['params'];
            
            // and now, see if we have to validate the value as well
            $method = 'validate_' . $this->validationType;
            if (!method_exists($this, $method)) {
                throw new Application_Exception(
                    'Unknown validation type.',
                    sprintf(
                        'Cannot validate using type [%s], the target method [%s] does not exist in class [%s].',
                        $this->validationType,
                        $method,
                        get_class($this)
                    ),
                    self::ERROR_UNKNOWN_VALIDATION_TYPE
                );
            }
         
            if($this->valueType === self::VALUE_TYPE_COMMA_SEPARATED) {
                if(!is_array($value)) {
                    $value = explode(',', $value);
                }
                
                $keep = array();
                foreach($value as $subval) {
                    $subval = $this->$method($subval);
                    if($subval !== null) {
                        $keep[] = $subval;
                    }
                }
                
                $value = $keep;
            } else {
                $value = $this->$method($value);
            }
        }

        $this->validated = true;
        $this->validatedValue = $value;
        
        return $value;
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
        return $this->setValidation(self::VALIDATION_TYPE_REGEX, $regex);
    }
    
    public function setURL()
    {
        return $this->setValidation(self::VALIDATION_TYPE_URL);
    }
    
    const VALUE_TYPE_STRING = 'string';
    
    const VALUE_TYPE_COMMA_SEPARATED = 'comma_separated';
    
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
        $this->valueType = self::VALUE_TYPE_COMMA_SEPARATED;
        return $this->setRegex('/\A(?:[0-9]+,)+[0-9]+|[0-9]+\z/six');
    }
    
   /**
    * Sets the variable to be an alias, as defined by the
    * {@link UI_Form::REGEX_ALIAS} regular expression.
    * 
    * @return Request_Param
    */
    public function setAlias()
    {
        require_once 'UI/Form.php';
        return $this->setRegex(UI_Form::REGEX_ALIAS);
    }

    /**
     * Sets the variable to be a name or title, as defined by the
     * {@link UI_Form::REGEX_NAME_OR_TITLE} regular expression.
     *
     * @return Request_Param
     */
    public function setNameOrTitle()
    {
        require_once 'UI/Form.php';
        return $this->setRegex(UI_Form::REGEX_NAME_OR_TITLE);
    }
    
    /**
     * Sets the variable to be a name or title, as defined by the
     * {@link UI_Form::REGEX_LABEL} regular expression.
     *
     * @return Request_Param
     */
    public function setLabel()
    {
        require_once 'UI/Form.php';
        return $this->setRegex(UI_Form::REGEX_LABEL);
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
     * Sets the parameter value as a list of possible values.
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
        if (is_array($args[0])) {
            $args = $args[0];
        }

        return $this->setValidation(self::VALIDATION_TYPE_ENUM, $args);
    }
    
    public function setContentLocale()
    {
        return $this->setEnum(Localization::getContentLocaleNames());
    }

    public function setArray()
    {
        return $this->setValidation(self::VALIDATION_TYPE_ARRAY);
    }
    
   /**
    * The parameter is a string boolean representation. This means
    * it can be any of the following: "yes", "true", "no", "false".
    * The value is automatically converted to a boolean when retrieving
    * the parameter.
    * 
    * @return Request_Param
    */
    public function setBoolean()
    {
        $this->addCallbackFilter(array($this, 'applyFilter_boolean'));
        return $this->setEnum('yes', 'no', 'true', 'false');
    }
    
    protected function applyFilter_boolean($value)
    {
        if($value == 'yes' || $value == 'true') {
            return true;
        }
        
        return false;
    }
    
    public function setMD5()
    {
        require_once 'UI/Form.php';
        return $this->setRegex(UI_Form::REGEX_MD5);
    }

    protected $validations = array();
    
    /**
     * Sets the validation type to use. See the VALIDATION_TYPE_XX class
     * constants for a list of types to use, or use any of the setXX methods
     * directly as shorthand.
     *
     * @param string $type
     * @param mixed $params
     * @return Request_Param
     */
    public function setValidation($type, $params = null)
    {
        if (!in_array($type, self::$validationTypes)) {
            throw new Application_Exception(
                'Invalid validation type',
                sprintf(
                    'Tried setting the validation type to "%1$s". Possible validation types are: %2$s. Use the class constants VALIDATION_TYPE_XXX to set the desired validation type to avoid errors like this.',
                    $type,
                    implode(', ', self::$validationTypes)
                )
            );
        }

        $this->validations[] = array(
            'type' => $type,
            'params' => $params
        );

        return $this;
    }
    
    public function get($default=null)
    {
        $value = $this->validate($this->request->getParam($this->paramName));
        if($value != null && $value != '') {
            return $value;
        }
        
        return $default;
    }

    /**
     * Validates an integer: returns null if the value is not an integer.
     * @param int|array|string|NULL|object $value
     * @return int|NULL
     * @see setInteger()
     */
    protected function validate_integer($value)
    {
        if (is_array($value)) {
            return null;
        }

        $int = null;
        if (preg_match('/\A\d+\z/', $value)) {
            $int = intval($value);
        }

        return $int;
    }
    
   /**
    * Validates the syntax of an URL, but not its actual validity. 
    * @param string $value
    * @return NULL|string
    */
    protected function validate_url($value)
    {
        if(!is_string($value)) {
            return null;
        }
        
        $info = parse_url($value);
        if(isset($info['host'])) {
            return $value;
        }
        
        return null;
    }

    /**
     * Validates a numeric value: returns null if the value is not in numeric notation.
     * @param string $value
     * @return string|NULL
     * @see setNumeric()
     */
    protected function validate_numeric($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Validates a request value using a regex. Returns null if the value does not match.
     * @param string $value
     * @return string|NULL
     * @see setRegex()
     */
    protected function validate_regex($value)
    {
        if (preg_match($this->validationParams, $value)) {
            return $value;
        }

        return null;
    }

    /**
     * Validates a string containing only letters, lowercase and uppercase.
     * @param string $value
     * @return string|NULL
     * @see setAlpha()
     */
    protected function validate_alpha($value)
    {
        if (preg_match('/\A[a-zA-Z]+\z/', $value)) {
            return $value;
        }

        return null;
    }

    /**
     * Validates a string containing only letters, lowercase and uppercase, and numbers.
     * @param string $value
     * @return string|NULL
     * @see setAlnum()
     */
    protected function validate_alnum($value)
    {
        if (preg_match('/\A[a-zA-Z0-9]+\z/', $value)) {
            return $value;
        }
    
        return null;
    }
    
    /**
     * Validates the value according to a list of possible values.
     * @param string $value
     * @return string|NULL
     */
    protected function validate_enum($value)
    {
        if (in_array($value, $this->validationParams)) {
            return $value;
        }

        return null;
    }

    /**
     * Makes sure that the value is an array.
     * @param array $value
     * @return array|NULL
     */
    protected function validate_array($value)
    {
        if (is_array($value)) {
            return $value;
        }

        return null;
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
     */
    public function addFilter($type, $params = null)
    {
        if (!in_array($type, self::$filterTypes)) {
            throw new Application_Exception(
                'Invalid filter type',
                sprintf(
                    'Tried setting the filter type to "%1$s". Possible validation types are: %2$s. Use the class constants FILTER_XXX to set the desired validation type to avoid errors like this.',
                    $type,
                    implode(', ', self::$filterTypes)
                )
            );
        }

        $this->filters[] = array(
            'type' => $type,
            'params' => $params
        );

        return $this;
    }
    
    public function addFilterTrim()
    {
        return $this->addCallbackFilter('trim');
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
    public function addCallbackFilter($callback, $params = array())
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
     */
    public function addStripTagsFilter($allowedTags = '')
    {
        return $this->addCallbackFilter('strip_tags', array($allowedTags));
    }

    public function addHTMLSpecialcharsFilter()
    {
        return $this->addCallbackFilter('htmlspecialchars', array(ENT_QUOTES, 'UTF-8'));
    }

    public function getName()
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
    public function makeRequired()
    {
        $this->required = true;
        return $this;
    }
    
    public function isRequired()
    {
        return $this->required;
    }
    
    public function getValidationType()
    {
        return $this->validationType;
    }
}