<?php

declare(strict_types=1);

namespace AppUtils;

abstract class ConvertHelper_URLFinder_Detector
{
    public const RUN_BEFORE = 'before';
    public const RUN_AFTER = 'after';

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

    abstract public function getRunPosition() : string;

    abstract public function isValidFor(string $subject) : bool;

    abstract protected function filterSubject(string $subject) : string;

    /**
     * @param string $subject
     * @return string[]
     */
    abstract protected function detect(string $subject) : array;
}
