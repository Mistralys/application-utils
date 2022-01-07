<?php
/**
 * File containing the class {@see \AppUtils\HTMLTag\GlobalOptions}.
 *
 * @package AppUtils
 * @subpackage HTML
 * @see \AppUtils\HTMLTag\GlobalOptions
 */

declare(strict_types=1);

namespace AppUtils\HTMLTag;

use AppUtils\HTMLTag;
use AppUtils\Interface_Optionable;
use AppUtils\Traits_Optionable;

/**
 * Utility for setting global options for the tag builder
 * class `HTMLTag`.
 *
 * Usage to get the instance:
 *
 * <pre>
 * HTMLTag::getGlobalOptions();
 * </pre>
 *
 * @package AppUtils
 * @subpackage HTML
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see HTMLTag::getGlobalOptions()
 */
class GlobalOptions implements Interface_Optionable
{
    use Traits_Optionable;

    public const OPTION_SELF_CLOSE_STYLE = 'selfCloseStyle';

    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_SELF_CLOSE_STYLE => HTMLTag::SELF_CLOSE_STYLE_SLASH
        );
    }

    public function getSelfCloseStyle() : string
    {
        return $this->getStringOption(self::OPTION_SELF_CLOSE_STYLE);
    }

    public function setSelfCloseStyle(string $style) : GlobalOptions
    {
        return $this->setOption(self::OPTION_SELF_CLOSE_STYLE, $style);
    }

    public function setSelfCloseSlash() : GlobalOptions
    {
        return $this->setSelfCloseStyle(HTMLTag::SELF_CLOSE_STYLE_SLASH);
    }

    public function setSelfCloseNone() : GlobalOptions
    {
        return $this->setSelfCloseStyle(HTMLTag::SELF_CLOSE_STYLE_NONE);
    }
}
