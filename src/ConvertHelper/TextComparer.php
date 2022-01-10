<?php
/**
 * File containing the class {@see ConvertHelper_TextComparer}.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see ConvertHelper_TextComparer
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Text comparison tool: can be used to calculate how
 * close two texts are from each other, using the
 * Levenshtein method.
 *
 * Converts the resulting match rating to a percentage
 * for easy processing.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ConvertHelper::matchString()
 */
class ConvertHelper_TextComparer implements Interface_Optionable
{
    use Traits_Optionable;

    public const OPTION_MAX_LEVENSHTEIN_DISTANCE = 'maxLevenshtein';
    public const OPTION_PRECISION = 'precision';

    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_MAX_LEVENSHTEIN_DISTANCE => 10,
            self::OPTION_PRECISION => 1
        );
    }

    public function getMaxDistance() : int
    {
        return $this->getIntOption(self::OPTION_MAX_LEVENSHTEIN_DISTANCE);
    }

    public function getPrecision() : int
    {
        return $this->getIntOption(self::OPTION_PRECISION);
    }

    /**
     * Sets the maximum Levensthein distance: results above this
     * value are ignored (will return a 0% match).
     *
     * @param int $distance
     * @return ConvertHelper_TextComparer
     */
    public function setMaxDistance(int $distance) : ConvertHelper_TextComparer
    {
        return $this->setOption(self::OPTION_MAX_LEVENSHTEIN_DISTANCE, $distance);
    }

    /**
     * Sets the precision of the returned match percentage value.
     *
     * @param int $precision
     * @return ConvertHelper_TextComparer
     */
    public function setPrecision(int $precision) : ConvertHelper_TextComparer
    {
        return $this->setOption(self::OPTION_PRECISION, $precision);
    }

    /**
     * Calculates a percentage match of the source string with the target string.
     *
     * NOTE: The percentage is based on the maximum Levensthein distance
     * option. As such, the smaller the calculated distance, the higher
     * the percentage. The maximum distance equals to 0%.
     *
     * @param string $source
     * @param string $target
     * @return float
     */
    public function match(string $source, string $target) : float
    {
        // avoid doing this via levenshtein
        if($source === $target) {
            return 100;
        }

        $maxL = $this->getMaxDistance();

        $diff = levenshtein($source, $target);
        if($diff > $maxL) {
            return 0;
        }

        $percent = $diff * 100 / ($maxL + 1);
        return round(100 - $percent, $this->getPrecision());
    }
}
