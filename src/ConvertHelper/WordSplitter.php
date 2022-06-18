<?php
/**
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see \AppUtils\ConvertHelper\WordSplitter
 */

declare(strict_types=1);

namespace AppUtils\ConvertHelper;

/**
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class WordSplitter
{
    private string $subject;
    private bool $removeDuplicates = false;
    private bool $sorting = false;
    private int $minWordLength = 0;
    private bool $duplicatesCaseInsensitive;

    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }

    public function setRemoveDuplicates(bool $remove=true, bool $caseInsensitive=false) : self
    {
        $this->removeDuplicates = $remove;
        $this->duplicatesCaseInsensitive = $caseInsensitive;
        return $this;
    }

    public function setSorting(bool $sorting=true) : self
    {
        $this->sorting = $sorting;
        return $this;
    }

    public function setMinWordLength(int $length) : self
    {
        $this->minWordLength = $length;
        return $this;
    }

    public function split() : array
    {
        $words = preg_split("/\W+/", $this->subject);

        $words = $this->filterEmpty($words);

        if($this->removeDuplicates) {
            $words = $this->filterDuplicates($words);
        }

        if($this->sorting) {
            usort($words, 'strnatcasecmp');
        }

        return $words;
    }

    private function filterDuplicates(array $words) : array
    {
        if($this->duplicatesCaseInsensitive) {
            return $this->filterDuplicatesCaseInsensitive($words);
        }

        return array_unique($words);
    }

    private function filterDuplicatesCaseInsensitive(array $array) : array
    {
        return array_intersect_key(
            $array,
            array_unique( array_map( "strtolower", $array ) )
        );
    }

    /**
     * @param string[] $words
     * @return string[]
     */
    private function filterEmpty(array $words) : array
    {
        $keep = array();

        foreach($words as $word)
        {
            if(empty($word)) {
                continue;
            }

            if(mb_strlen($word) < $this->minWordLength) {
                continue;
            }

            $keep[] = $word;
        }

        return $keep;
    }
}
