<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_URLFinder} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_URLFinder
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Can find any URLs in a string, be it plain text or HTML, XML.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see ConvertHelper::createURLFinder()
 */
class ConvertHelper_URLFinder implements Interface_Optionable
{
    const ERROR_INVALID_DETECTOR_CLASS = 87901;

    use Traits_Optionable;
    
    /**
     * @var array<string,URLInfo>
     */
    private $urls = array();

    /**
     * @var array<string,URLInfo>
     */
    private $emails = array();

    /**
     * @var array<string,bool>
     */
    private $enabledDetectorClasses = array(
        ConvertHelper_URLFinder_Detector_Tel::class => true,
        ConvertHelper_URLFinder_Detector_HTMLAttributes::class => false,
        ConvertHelper_URLFinder_Detector_IPV4::class => true
    );

    /**
     * @var string[]
     */
    private $matches = array();

    /**
     * @var string[]
     */
    private $boundaries = array(
        "\n",
        "\t",
        "\r",
        '"',
        "'",
        '|',
        ',',
        ';',
        '<',
        '>'
    );

    /**
     * @var ConvertHelper_URLFinder_Detector[]
     */
    private $detectors = array();

    /**
     * @var string
     */
    private $subject;

    /**
     * @var bool
     */
    private $parsed = false;

    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'include-emails' => false,
            'omit-mailto' => false,
            'sorting' => false,
            'normalize' => false
        );
    }

    /**
     * Whether all URLs should be normalized (parameters ordered alphabetically,
     * whitespace removal). This ensures that URL duplicates are detected even
     * if they have a different order of parameters.
     *
     * @param bool $enabled
     * @return $this
     */
    public function enableNormalizing(bool $enabled=true) : ConvertHelper_URLFinder
    {
        $this->setOption('normalize', $enabled);
        return $this;
    }
    
   /**
    * Whether to enable sorting the URLs alphabetically (disabled by default).
    * 
    * @param bool $enabled
    * @return $this
    */
    public function enableSorting(bool $enabled=true) : ConvertHelper_URLFinder
    {
        $this->setOption('sorting', $enabled);
        return $this;
    }
    
   /**
    * Whether to include email addresses in the search. 
    * This is only relevant when using the getURLs()
    * method.
    * 
    * @param bool $include
    * @return ConvertHelper_URLFinder
    */
    public function includeEmails(bool $include=true) : ConvertHelper_URLFinder
    {
        $this->setOption('include-emails', $include);
        return $this;
    }
    
   /**
    * Whether to omit the mailto: that is automatically added to all email addresses.
    * 
    * @param bool $omit
    * @return ConvertHelper_URLFinder
    */
    public function omitMailto(bool $omit=true) : ConvertHelper_URLFinder
    {
        $this->setOption('omit-mailto', $omit);
        return $this;
    }

    /**
     * Splits the string by a list of word boundaries, so that all relevant
     * words are separated into individual lines. Each line is then checked
     * to keep only strings that are more or less likely to contain a domain name.
     *
     * @param string $subject
     * @return string[]
     */
    private function splitSubject(string $subject) : array
    {
        $subject = str_replace($this->boundaries, ' ', $subject);
        $lines = ConvertHelper::explodeTrim(' ', $subject);

        unset($subject);

        $keep = array();

        foreach ($lines as $line)
        {
            $line = $this->analyzeLine($line);

            if($line !== null) {
                $keep[] = $line;
            }
        }

        return array_unique($keep);
    }

    /**
     * Analyzes a single line to see if it is likely to contain a domain name.
     *
     * @param string $line
     * @return string|null
     */
    private function analyzeLine(string $line) : ?string
    {
        // Strip punctuation from the beginning and end,
        // to exclude the end of phrases, e.g. "domain.com."
        $line = trim($line, '.');

        // Handle detecting an URI scheme
        if(strstr($line, ':') !== false)
        {
            $scheme = URLInfo_Schemes::detectScheme($line);

            if ($scheme !== null)
            {
                return $line;
            }
        }

        // From here on out, the only things we can still
        // detect are email addresses and domain names.

        // No dot? Then it's certainly not a domain name.
        if(strstr($line, '.') === false) {
            return null;
        }

        return $line;
    }

    /**
     * Filters the subject string before trying to detect regular HTTP/HTTPS
     * URLs as well as email addresses that are domain-based.
     *
     * @param string $subject
     * @return string
     */
    private function filterSubjectBefore(string $subject) : string
    {
        $subject = stripslashes($subject);

        foreach($this->detectors as $detector)
        {
            // Avoid processing the string if it is not needed.
            if($detector->getRunPosition() !== ConvertHelper_URLFinder_Detector::RUN_BEFORE || !$detector->isValidFor($subject)) {
                continue;
            }

            $subject = $detector->processString($subject);

            $this->matches = array_merge($this->matches, $detector->getMatches());
        }

        return $subject;
    }

    /**
     * @param string $className
     * @return ConvertHelper_URLFinder_Detector
     * @throws ConvertHelper_Exception
     */
    private function createDetector(string $className) : ConvertHelper_URLFinder_Detector
    {
        $detector = new $className();

        if($detector instanceof ConvertHelper_URLFinder_Detector)
        {
            return $detector;
        }

        throw new ConvertHelper_Exception(
            'Not a valid detector class.',
            sprintf(
                'The class [%s] is not an instance of [%s].',
                $className,
                ConvertHelper_URLFinder_Detector::class
            ),
            self::ERROR_INVALID_DETECTOR_CLASS
        );
    }

   /**
    * Fetches all URLs that can be found in the subject string.
    * 
    * @return string[]
    */
    public function getURLs() : array
    {
        $this->parse();

        $result = $this->getItemsAsString($this->urls);

        if($this->getBoolOption('include-emails'))
        {
            $result = array_merge($result, $this->getEmails());
        }

        if($this->getBoolOption('sorting'))
        {
            usort($result, function(string $a, string $b) {
                return strnatcasecmp($a, $b);
            });
        }

        return $result;
    }

    /**
     * @param array<string,URLInfo> $collection
     * @return string[]
     */
    private function getItemsAsString(array $collection) : array
    {
        $normalize = $this->getBoolOption('normalize');

        $result = array();

        foreach($collection as $url => $info) {
            if($normalize) {
                $url = $info->getNormalized();
            }

            if(!in_array($url, $result)) {
                $result[] = $url;
            }
        }

        return $result;
    }

    /**
     * Instantiates the selected detector classes, which are
     * used to detect specific elements in the target string
     * (beyond regular URLs and Email addresses).
     *
     * @throws ConvertHelper_Exception
     */
    private function initDetectors() : void
    {
        foreach($this->enabledDetectorClasses as $className => $enabled)
        {
            if($enabled) {
                $this->detectors[] = $this->createDetector($className);
            }
        }
    }

    /**
     * Parses the specified string to detect all URLs and Email addresses.
     * For accurate results, this does not use a regex, but splits the
     * string into a list of strings that are likely to be either an URL
     * or Email address. Each of these is then checked for a valid scheme
     * or domain name extension.
     */
    private function parse() : void
    {
        if($this->parsed) {
            return;
        }

        $this->parsed = true;

        $this->initDetectors();
        $this->detectMatches($this->subject);

        unset($this->subject);

        foreach($this->matches as $match)
        {
            $info = parseURL($match);

            if($info->isEmail())
            {
                $this->emails[$this->filterEmailAddress($match)] = $info;
                continue;
            }

            $this->urls[$match] = $info;
        }
    }

    /**
     * Enables the search for relative URLs in HTML attributes.
     *
     * @param bool $enable
     * @return $this
     */
    public function enableHTMLAttributes(bool $enable=true) : ConvertHelper_URLFinder
    {
        $this->enabledDetectorClasses[ConvertHelper_URLFinder_Detector_HTMLAttributes::class] = $enable;
        return $this;
    }

    /**
     * Ensures that the email address has the `mailto:` scheme prepended,
     * and lowercases it to avoid case mixups.
     *
     * @param string $email
     * @return string
     */
    private function filterEmailAddress(string $email) : string
    {
        if(stristr($email, 'mailto:') === false) {
            $email = 'mailto:'.$email;
        }

        return strtolower($email);
    }

    /**
     * Detects all URL and Email matches in the specified string.
     *
     * @param string $subject
     */
    private function detectMatches(string $subject) : void
    {
        $subject = $this->filterSubjectBefore($subject);

        $lines = $this->splitSubject($subject);
        $domains = new ConvertHelper_URLFinder_DomainExtensions();

        foreach ($lines as $line)
        {
            $scheme = URLInfo_Schemes::detectScheme($line);
            if($scheme !== null) {
                $this->matches[] = $line;
                continue;
            }

            $extension = $this->detectDomainExtension($line);

            if($domains->nameExists($extension)) {
                $this->matches[] = $line;
            }
        }

        $this->filterSubjectAfter($subject);
    }

    private function filterSubjectAfter(string $subject) : void
    {
        // Sort the matches from longest to shortest, to avoid
        // replacing parts of URLs.
        $remove = $this->matches;
        usort($remove, function(string $a, string $b) {
            return strlen($b) - strlen($a);
        });

        $subject = str_replace($remove, ' ', $subject);

        foreach($this->detectors as $detector)
        {
            if($detector->getRunPosition() !== ConvertHelper_URLFinder_Detector::RUN_AFTER || !$detector->isValidFor($subject)) {
                continue;
            }

            $subject = $detector->processString($subject);

            $this->matches = array_merge($this->matches, $detector->getMatches());
        }
    }

    /**
     * Attempts to extract a valid domain name extension from
     * the specified URL.
     *
     * @param string $url
     * @return string
     * @see ConvertHelper_URLFinder_DomainExtensions
     */
    private function detectDomainExtension(string $url) : string
    {
        $boundaries = array('/', '?');

        // Remove the path or query parts to access the domain extension only
        foreach($boundaries as $boundary) {
            if(strstr($url, $boundary)) {
                $parts = explode($boundary, $url);
                $url = array_shift($parts);
                break;
            }
        }

        $parts = explode('.', $url);

        return array_pop($parts);
    }

   /**
    * Retrieves all email addresses from the subject string.
    * 
    * @return string[]
    * 
    * @see omitMailto()
    */
    public function getEmails() : array
    {
        $this->parse();

        $result = $this->getItemsAsString($this->emails);

        if($this->getBoolOption('omit-mailto')) {
            $keep = array();
            foreach($result as $email) {
                $keep[] = str_replace('mailto:', '', $email);
            }

            $result = $keep;
        }

        if($this->getBoolOption('sorting'))
        {
            usort($result, function(string $a, string $b) {
                return strnatcasecmp($a, $b);
            });
        }
        
        return $result;
    }
    
   /**
    * Retrieves all URLs as URLInfo instances.
    * 
    * @return URLInfo[]
    */
    public function getInfos() : array
    {
        $this->parse();

        $result = array();
        $normalize = $this->getBoolOption('normalize');

        foreach($this->urls as $url => $info)
        {
            if($normalize) {
                $url = $info->getNormalized();
            }

            $result[$url] = $info;
        }

        if($this->getBoolOption('sorting'))
        {
            ksort($result);
        }

        return array_values($result);
    }
}
