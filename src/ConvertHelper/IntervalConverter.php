<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_IntervalConverter} class.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_IntervalConverter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Converts date intervals to human-readable strings.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>

 * @see ConvertHelper::interval2string()
 */
class ConvertHelper_IntervalConverter
{
    const ERROR_MISSING_TRANSLATION = 43501;
    
   /**
    * @var array<string,string>|NULL
    */
    protected static $texts = null;
    
   /**
    * @var string[]
    */
    protected $tokens = array('y', 'm', 'd', 'h', 'i', 's');
    
    public function __construct()
    {
        if(class_exists('\AppLocalize\Localization')) {
            \AppLocalize\Localization::onLocaleChanged(array($this, 'handle_localeChanged'));
        }
    }
    
   /**
    * Called whenever the application locale has changed,
    * to reset the internal translation cache.
    */
    public function handle_localeChanged() : void
    {
        self::$texts = null;
    }
    
   /**
    * Converts the specified interval to a human-readable
    * string, e.g. "2 hours and 4 minutes".
    * 
    * @param \DateInterval $interval
    * @return string
    * @throws ConvertHelper_Exception
    * 
    * @see ConvertHelper_IntervalConverter::ERROR_MISSING_TRANSLATION
    */
    public function toString(\DateInterval $interval) : string
    {
        $this->initTexts();
        
        $interval = parseInterval($interval);
        
        $keep = $this->resolveTokens($interval);

        $parts = array();
        foreach($keep as $token)
        {
            $value = $interval->getToken($token);
            if($value === 0) {
                continue;
            }
            
            $parts[] = $this->translateToken($token, $interval);
        }
        
        if(count($parts) == 1) {
            return $parts[0];
        }
        
        $last = array_pop($parts);
        
        return t('%1$s and %2$s', implode(', ', $parts), $last);
    }
    
   /**
    * Translates the selected time token, e.g. "y" (for year).
    * 
    * @param string $token
    * @param ConvertHelper_DateInterval $interval
    * @throws ConvertHelper_Exception
    * @return string
    */
    protected function translateToken(string $token, ConvertHelper_DateInterval $interval) : string
    {
        $value = $interval->getToken($token);
        
        $suffix = 'p';
        if($value == 1) { $suffix = 's'; }
        $token .= $suffix;
        
        if(!isset(self::$texts[$token]))
        {
            throw new ConvertHelper_Exception(
                'Missing interval translation',
                sprintf(
                    'The format [%s] does not exist in the texts.',
                    $token
                ),
                self::ERROR_MISSING_TRANSLATION
            );
        }
        
        return str_replace(
            '$value', 
            (string)$value, 
            self::$texts[$token]
        );
    }
    
   /**
    * Resolves all time tokens that need to be translated in
    * the subject interval, depending on its length.
    * 
    * @param ConvertHelper_DateInterval $interval
    * @return string[]
    */
    protected function resolveTokens(ConvertHelper_DateInterval $interval) : array
    {
        $offset = 0;
        
        foreach($this->tokens as $token) 
        {
            if($interval->getToken($token) > 0) 
            {
                return array_slice($this->tokens, $offset);
            }
            
            $offset++;
        }
        
        return array();
    }
    
   /**
    * Initializes the translateable strings.
    */
    protected function initTexts() : void
    {
        if(isset(self::$texts)) {
            return;
        }
        
        self::$texts = array(
            'ys' => t('1 year'), 
            'yp' => t('%1$s years', '$value'), 
            'ms' => t('1 month'), 
            'mp' => t('%1$s months', '$value'), 
            'ds' => t('1 day'), 
            'dp' => t('%1$s days', '$value'), 
            'hs' => t('1 hour'), 
            'hp' => t('%1$s hours', '$value'), 
            'is' => t('1 minute'), 
            'ip' => t('%1$s minutes', '$value'), 
            'ss' => t('1 second'), 
            'sp' => t('%1$s seconds', '$value'), 
        );
    }
}
