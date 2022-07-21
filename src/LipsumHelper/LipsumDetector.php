<?php
/**
 * @package Application Utils
 * @subpackage LipsumHelper
 * @see \AppUtils\LipsumHelper\LipsumDetector
 */

declare(strict_types=1);

namespace AppUtils\LipsumHelper;

/**
 * Lipsum dummy text detection tool.
 *
 * @package Application Utils
 * @subpackage LipsumHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class LipsumDetector
{
    /**
     * @var string[]
     */
    private static array $words = array(
        'exercitationem',
        'reprehenderit',
        'perspiciatis',
        'exercitation',
        'consequuntur',
        'accusantium',
        'consectetur',
        'consequatur',
        'laudantium',
        'incididunt',
        'doloremque',
        'laboriosam',
        'voluptatem',
        'adipiscing',
        'aspernatur',
        'architecto',
        'cupidatat',
        'inventore',
        'explicabo',
        'Excepteur',
        'molestiae',
        'voluptate',
        'veritatis',
        'consequat',
        'proident',
        'adipisci',
        'incidunt',
        'deserunt',
        'quisquam',
        'nesciunt',
        'voluptas',
        'suscipit',
        'occaecat',
        'corporis',
        'pariatur',
        'aliquam',
        'laborum',
        'laboris',
        'numquam',
        'nostrum',
        'nostrud',
        'aliquid',
        'quaerat',
        'aperiam',
        'eiusmod',
        'dolores',
        'dolorem',
        'ullamco',
        'aliquip',
        'aliqua',
        'veniam',
        'cillum',
        'labore',
        'mollit',
        'beatae',
        'fugiat',
        'totam',
        'ullam',
        'velit',
        'dolor',
        'dicta',
        'culpa',
        'vitae',
        'autem',
        'sequi',
        'natus',
        'fugit',
        'Neque',
        'quasi',
        'ipsam',
        'ipsum',
        'porro',
        'irure',
        'Lorem',
        'magna',
        'magni',
        'minim',
        'nihil',
        'illum',
        'dolor',
        'sit',
        'amet',
        'elit'
    );

    private int $minWords = 2;
    private string $subject;
    private bool $detected = false;
    private int $count = 0;

    /**
     * @var string[]
     */
    private array $found = array();

    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }

    public function setMinWords(int $min) : self
    {
        // Avoid a reset if the value is the same
        if($this->minWords === $min)
        {
            return $this;
        }

        $this->minWords = $min;

        $this->reset();

        return $this;
    }

    private function reset() : void
    {
        $this->found = array();
        $this->detected = false;
    }

    private function detect() : void
    {
        if($this->detected) {
            return;
        }

        $this->detected = true;
        $this->found = array();
        $this->count = 0;

        foreach(self::$words as $word)
        {
            if(stripos($this->subject, $word) !== false)
            {
                $this->count++;
                $this->found[] = $word;
            }

            if($this->count >= $this->minWords) {
                break;
            }
        }
    }

    public function isDetected() : bool
    {
        return $this->count >= $this->minWords;
    }

    /**
     * @return string[]
     */
    public function getDetectedWords() : array
    {
        $this->detect();

        return $this->found;
    }

    public function countDetectedWords() : int
    {
        $this->detect();

        return $this->count;
    }
}
