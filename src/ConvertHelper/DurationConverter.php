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
        
        // Calculate the difference in seconds betweeen
        // the two timestamps
        
        $difference = $this->dateTo - $this->dateFrom;
        
        $interval = "";
        
        $future = false;
        if($difference < 0) 
        {
            $difference = $difference * -1;
            $future = true;
        }
        
        // If difference is less than 60 seconds,
        // seconds is a good interval of choice
        
        if ($difference < 60) {
            $interval = "s";
        }
        
        // If difference is between 60 seconds and
        // 60 minutes, minutes is a good interval
        elseif ($difference >= 60 && $difference < 60 * 60) {
            $interval = "n";
        }
        
        // If difference is between 1 hour and 24 hours
        // hours is a good interval
        elseif ($difference >= 60 * 60 && $difference < 60 * 60 * 24) {
            $interval = "h";
        }
        
        // If difference is between 1 day and 7 days
        // days is a good interval
        elseif ($difference >= 60 * 60 * 24 && $difference < 60 * 60 * 24 * 7) {
            $interval = "d";
        }
        
        // If difference is between 1 week and 30 days
        // weeks is a good interval
        elseif ($difference >= 60 * 60 * 24 * 7 && $difference < 60 * 60 * 24 * 30) {
            $interval = "ww";
        }
        
        // If difference is between 30 days and 365 days
        // months is a good interval, again, the same thing
        // applies, if the 29th February happens to exist
        // between your 2 dates, the function will return
        // the 'incorrect' value for a day
        elseif ($difference >= 60 * 60 * 24 * 30 && $difference < 60 * 60 * 24 * 365) {
            $interval = "m";
        }
        
        // If difference is greater than or equal to 365
        // days, return year. This will be incorrect if
        // for example, you call the function on the 28th April
        // 2008 passing in 29th April 2007. It will return
        // 1 year ago when in actual fact (yawn!) not quite
        // a year has gone by
        elseif ($difference >= 60 * 60 * 24 * 365) {
            $interval = "y";
        }
        
        $result = '';
        
        // Based on the interval, determine the
        // number of units between the two dates
        // From this point on, you would be hard
        // pushed telling the difference between
        // this function and DateDiff. If the $datediff
        // returned is 1, be sure to return the singular
        // of the unit, e.g. 'day' rather 'days'
        switch ($interval)
        {
            case "m":
                $months_difference = (int)floor($difference / 60 / 60 / 24 / 29);
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
                
                if($future) {
                    $result = ($datediff == 1) ? t('In one month', $datediff) : t('In %1s months', $datediff);
                } else {
                    $result = ($datediff == 1) ? t('One month ago', $datediff) : t('%1s months ago', $datediff);
                }
                break;
                
            case "y":
                $datediff = floor($difference / 60 / 60 / 24 / 365);
                if($future) {
                    $result = ($datediff == 1) ? t('In one year', $datediff) : t('In %1s years', $datediff);
                } else {
                    $result = ($datediff == 1) ? t('One year ago', $datediff) : t('%1s years ago', $datediff);
                }
                break;
                
            case "d":
                $datediff = floor($difference / 60 / 60 / 24);
                if($future) {
                    $result = ($datediff == 1) ? t('In one day', $datediff) : t('In %1s days', $datediff);
                } else {
                    $result = ($datediff == 1) ? t('One day ago', $datediff) : t('%1s days ago', $datediff);
                }
                break;
                
            case "ww":
                $datediff = floor($difference / 60 / 60 / 24 / 7);
                if($future) {
                    $result = ($datediff == 1) ? t('In one week', $datediff) : t('In %1s weeks', $datediff);
                } else {
                    $result = ($datediff == 1) ? t('One week ago', $datediff) : t('%1s weeks ago', $datediff);
                }
                break;
                
            case "h":
                $datediff = floor($difference / 60 / 60);
                if($future) {
                    $result = ($datediff == 1) ? t('In one hour', $datediff) : t('In %1s hours', $datediff);
                } else {
                    $result = ($datediff == 1) ? t('One hour ago', $datediff) : t('%1s hours ago', $datediff);
                }
                break;
                
            case "n":
                $datediff = floor($difference / 60);
                if($future) {
                    $result = ($datediff == 1) ? t('In one minute', $datediff) : t('In %1s minutes', $datediff);
                } else {
                    $result = ($datediff == 1) ? t('One minute ago', $datediff) : t('%1s minutes ago', $datediff);
                }
                break;
                
            case "s":
                $datediff = $difference;
                if($future) {
                    $result = ($datediff == 1) ? t('In one second', $datediff) : t('In %1s seconds', $datediff);
                } else {
                    $result = ($datediff == 1) ? t('One second ago', $datediff) : t('%1s seconds ago', $datediff);
                }
                break;
        }
        
        return $result;
    }
}
