<?php
/**
 * File containing the {@see \AppUtils\ConvertHelper_DateInterval} class.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see \AppUtils\ConvertHelper_DateInterval
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * DateInterval wrapper, that makes it much easier to
 * work with intervals. The methods are typed, so no
 * conversions are necessary. A number of utility methods
 * also help.
 * 
 * Automatically fixes the issue of date interval properties
 * not being populated entirely when it is created using
 * a format string.
 * 
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see \AppUtils\parseInterval()
 */
class ConvertHelper_DateInterval
{
    const ERROR_CANNOT_GET_DATE_DIFF = 43601;
    
    const TOKEN_SECONDS = 's';
    const TOKEN_MINUTES = 'i';
    const TOKEN_HOURS = 'h';
    const TOKEN_DAYS = 'd';
    const TOKEN_MONTHS = 'm';
    const TOKEN_YEARS = 'y';
    
   /**
    * @var \DateInterval
    */
    protected $interval;
    
   /**
    * @var int
    */
    protected $seconds;
    
    protected function __construct(int $seconds)
    {
        $this->seconds = $seconds;
        
        $d1 = new \DateTime();
        $d2 = new \DateTime();
        $d2->add(new \DateInterval('PT'.$this->seconds.'S'));
        
        $interval = $d2->diff($d1);
        
        if($interval === false) 
        {
            throw new ConvertHelper_Exception(
                'Cannot create interval',
                sprintf('Getting the date diff failed to retrieve the interval for [%s] seconds.', $this->seconds),
                self::ERROR_CANNOT_GET_DATE_DIFF
            );
        }
        
        $this->interval = $interval;
    }
    
   /**
    * Creates the interval from a specific amount of seconds.
    * 
    * @param int $seconds
    * @return \AppUtils\ConvertHelper_DateInterval
    */
    public static function fromSeconds(int $seconds)
    {
        return new ConvertHelper_DateInterval($seconds);
    }
    
   /**
    * Creates the interval from an existing regular interval instance.
    * 
    * @param \DateInterval $interval
    * @return \AppUtils\ConvertHelper_DateInterval
    */
    public static function fromInterval(\DateInterval $interval)
    {
        return self::fromSeconds(ConvertHelper::interval2seconds($interval));
    }
    
   /**
    * Retrieves the PHP native date interval.
    * 
    * @return \DateInterval
    */
    public function getInterval() : \DateInterval
    {
        return $this->interval;
    }
    
    public function getSeconds() : int
    {
        return $this->getToken(self::TOKEN_SECONDS);
    }
    
    public function getHours() : int
    {
        return $this->getToken(self::TOKEN_HOURS);
    }
    
    public function getMinutes() : int
    {
        return $this->getToken(self::TOKEN_MINUTES);
    }
    
    public function getDays() : int
    {
        return $this->getToken(self::TOKEN_DAYS);
    }
    
    public function getMonths() : int
    {
        return $this->getToken(self::TOKEN_MONTHS);
    }
    
    public function getYears() : int
    {
        return $this->getToken(self::TOKEN_YEARS);
    }
    
   /**
    * Retrieves a specific time token, e.g. "h" (for hours).
    * Using the constants to specifiy the tokens is recommended.
    * 
    * @param string $token
    * @return int
    * 
    * @see ConvertHelper_DateInterval::TOKEN_SECONDS
    * @see ConvertHelper_DateInterval::TOKEN_MINUTES
    * @see ConvertHelper_DateInterval::TOKEN_HOURS
    * @see ConvertHelper_DateInterval::TOKEN_DAYS
    * @see ConvertHelper_DateInterval::TOKEN_MONTHS
    * @see ConvertHelper_DateInterval::TOKEN_YEARS
    */
    public function getToken(string $token) : int
    {
        return (int)$this->interval->$token;
    }
    
   /**
    * The total amount of seconds in the interval (including
    * everything, from seconds to days, months, years...).
    * 
    * @return int
    */
    public function getTotalSeconds() : int
    {
        return $this->seconds;
    }
}
