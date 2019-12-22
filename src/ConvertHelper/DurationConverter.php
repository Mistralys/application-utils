<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_DurationConverter} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see ConvertHelper_DurationConverter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Converts a timespan to a human readable duration string,
 * e.g. "2 months", "4 minutes".
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @link http://www.sajithmr.com/php-time-ago-calculation/
 */
class ConvertHelper_DurationConverter
{
    const ERROR_NO_DATE_FROM_SET = 43401;
    
    
   /**
    * @var int
    */
    protected $dateFrom;
    
   /**
    * @var int
    */
    protected $dateTo;
    
   /**
    * @var bool
    */
    protected $future = false;
    
   /**
    * @var string
    */
    protected $interval = '';
    
   /**
    * @var int
    */
    protected $difference = 0;
    
   /**
    * @var int
    */
    protected $dateDiff = 0;
    
   /**
    * @var array
    */
    protected static $texts;
    
    public function __construct()
    {
    }
    
   /**
    * Sets the origin date to calculate from.
    * 
    * NOTE: if this is further in the future than
    * the to: date, it will be considered as a 
    * calculation for something to come, i.e. 
    * "In two days".
    *  
    * @param \DateTime $date
    * @return ConvertHelper_DurationConverter
    */
    public function setDateFrom(\DateTime $date) : ConvertHelper_DurationConverter
    {
        $this->dateFrom = ConvertHelper::date2timestamp($date);
        
        return $this;
    }
    
   /**
    * Sets the date to calculate to. Defaults to 
    * the current time if not set.
    * 
    * @param \DateTime $date
    * @return ConvertHelper_DurationConverter
    */
    public function setDateTo(\DateTime $date) : ConvertHelper_DurationConverter
    {
        $this->dateTo = ConvertHelper::date2timestamp($date);
        
        return $this;
    }
    
   /**
    * Converts the specified dates to a human readable string.
    * 
    * @throws ConvertHelper_Exception
    * @return string
    * 
    * @see ConvertHelper_DurationConverter::ERROR_NO_DATE_FROM_SET
    */
    public function convert() : string
    {
        $this->initTexts();
        $this->resolveCalculations();
        
        $epoch = 'past';
        $key = 'singular';
        if($this->dateDiff > 1) {
            $key = 'plural';
        }
        
        if($this->future) {
            $epoch = 'future'; 
        }
        
        $key .= '-'.$epoch;
        
        $text = self::$texts[$this->interval][$key];
        
        return str_replace('$value', $this->dateDiff, $text);
    }
    
    protected function initTexts()
    {
        if(isset(self::$texts)) {
            return;
        }
        
        self::$texts = array(
            'y' => array(
                'singular-future' => t('In one year'),
                'plural-future' => t('In %1s years', '$value'),
                'singular-past' => t('One year ago'),
                'plural-past' => t('%1s years ago', '$value')
            ),
            'm' => array(
                'singular-future' => t('In one month'),
                'plural-future' => t('In %1s months', '$value'),
                'singular-past' => t('One month ago'),
                'plural-past' => t('%1s months ago', '$value')
            ),
            'ww' => array(
                'singular-future' => t('In one week'),
                'plural-future' => t('In %1s weeks', '$value'),
                'singular-past' => t('One week ago'),
                'plural-past' => t('%1s weeks ago', '$value')
            ),
            'd' => array(
                'singular-future' => t('In one day'),
                'plural-future' => t('In %1s days', '$value'),
                'singular-past' => t('One day ago'),
                'plural-past' => t('%1s days ago', '$value')
            ),
            'h' => array(
                'singular-future' => t('In one hour'),
                'plural-future' => t('In %1s hours', '$value'),
                'singular-past' => t('One hour ago'),
                'plural-past' => t('%1s hours ago', '$value')
            ),
            'n' => array(
                'singular-future' => t('In one minute'),
                'plural-future' => t('In %1s minutes', '$value'),
                'singular-past' => t('One minute ago'),
                'plural-past' => t('%1s minutes ago', '$value')
            ),
            's' => array(
                'singular-future' => t('In one second'),
                'plural-future' => t('In %1s seconds', '$value'),
                'singular-past' => t('One second ago'),
                'plural-past' => t('%1s seconds ago', '$value')
            )
        );
    }
    
    protected function convert_minute() : int
    {
        return (int)floor($this->difference / 60);
    }
    
    protected function convert_hour() : int
    {
        return (int)floor($this->difference / 60 / 60);
    }
    
    protected function convert_week() : int
    {
        return (int)floor($this->difference / 60 / 60 / 24 / 7);
    }
    
    protected function convert_day() : int
    {
        return (int)floor($this->difference / 60 / 60 / 24);
    }
    
    protected function convert_year() : int
    {
        return (int)floor($this->difference / 60 / 60 / 24 / 365);
    }
    
    protected function convert_month() : int
    {
        $months_difference = (int)floor($this->difference / 60 / 60 / 24 / 29);
        
        $hour = (int)date("H", $this->dateFrom);
        $min = (int)date("i", $this->dateFrom);
        $sec = (int)date("s", $this->dateFrom);
        $month = (int)date("n", $this->dateFrom);
        $day = (int)date("j", $this->dateTo);
        $year = (int)date("Y", $this->dateFrom);
        
        while(mktime($hour, $min, $sec, $month + ($months_difference), $day, $year) < $this->dateTo)
        {
            $months_difference++;
        }
        
        $datediff = $months_difference;
        
        // We need this in here because it is possible
        // to have an 'm' interval and a months
        // difference of 12 because we are using 29 days
        // in a month
        if ($datediff == 12) {
            $datediff--;
        }
        
        return $datediff;
    }
    
    protected function resolveCalculations() : void
    {
        if(!isset($this->dateFrom))
        {
            throw new ConvertHelper_Exception(
                'No date from has been specified.',
                null,
                self::ERROR_NO_DATE_FROM_SET
            );
        }
        
        // no date to set? Assume we want to use today.
        if(!isset($this->dateTo))
        {
            $this->dateTo = time();
        }
        
        $this->difference = $this->resolveDifference();
        $this->interval = $this->resolveInterval();
        $this->dateDiff = $this->resolveDateDiff();
    }
    
    protected function resolveInterval() : string
    {
        // If difference is less than 60 seconds,
        // seconds is a good interval of choice
        
        if ($this->difference < 60) 
        {
            return "s";
        }
        
        // If difference is between 60 seconds and
        // 60 minutes, minutes is a good interval
        if ($this->difference >= 60 && $this->difference < 60 * 60) 
        {
            return "n";
        }
        
        // If difference is between 1 hour and 24 hours
        // hours is a good interval
        if ($this->difference >= 60 * 60 && $this->difference < 60 * 60 * 24) 
        {
            return "h";
        }
        
        // If difference is between 1 day and 7 days
        // days is a good interval
        if ($this->difference >= 60 * 60 * 24 && $this->difference < 60 * 60 * 24 * 7) 
        {
            return "d";
        }
        
        // If difference is between 1 week and 30 days
        // weeks is a good interval
        if ($this->difference >= 60 * 60 * 24 * 7 && $this->difference < 60 * 60 * 24 * 30) 
        {
            return "ww";
        }
        
        // If difference is between 30 days and 365 days
        // months is a good interval, again, the same thing
        // applies, if the 29th February happens to exist
        // between your 2 dates, the function will return
        // the 'incorrect' value for a day
        if ($this->difference >= 60 * 60 * 24 * 30 && $this->difference < 60 * 60 * 24 * 365) 
        {
            return "m";
        }
        
        // If difference is greater than or equal to 365
        // days, return year. This will be incorrect if
        // for example, you call the function on the 28th April
        // 2008 passing in 29th April 2007. It will return
        // 1 year ago when in actual fact (yawn!) not quite
        // a year has gone by
        if ($this->difference >= 60 * 60 * 24 * 365) 
        {
            return "y";
        }
        
        return ""; 
    }
    
    protected function resolveDifference() : int
    {
        // Calculate the difference in seconds betweeen
        // the two timestamps
        
        $difference = $this->dateTo - $this->dateFrom;
        
        if($difference < 0)
        {
            $difference = $difference * -1;
            $this->future = true;
        }
        
        return $difference;
    }
    
    protected function resolveDateDiff() : int
    {
        // Based on the interval, determine the
        // number of units between the two dates
        // From this point on, you would be hard
        // pushed telling the difference between
        // this function and DateDiff. If the $datediff
        // returned is 1, be sure to return the singular
        // of the unit, e.g. 'day' rather 'days'
        switch ($this->interval)
        {
            case "m":
                return $this->convert_month();
                
            case "y":
                return $this->convert_year();
                
            case "d":
                return $this->convert_day();
                
            case "ww":
                return $this->convert_week();
                
            case "h":
                return $this->convert_hour();
                
            case "n":
                return $this->convert_minute();
        }
        
        // seconds
        return $this->difference;
    }
}
