<?php

declare(strict_types=1);

namespace AppUtils;

abstract class ConvertHelper_URLFinder_Detector
{
    /**
     * @var string[]
     */
    private $matches = array();

    public function processString(string $subject) : string
    {
        $subject = $this->filterSubject($subject);

        $this->matches = $this->detect($subject);

        return str_replace($this->matches, ' ', $subject);
    }

    /**
     * @return string[]
     */
    public function getMatches() : array
    {
        return $this->matches;
    }

    abstract public function getScheme() : string;

    abstract protected function filterSubject(string $subject) : string;

    /**
     * @param string $subject
     * @return string[]
     */
    abstract protected function detect(string $subject) : array;
}
