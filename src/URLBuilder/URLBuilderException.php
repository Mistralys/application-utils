<?php
/**
 * @package Application Utils
 * @subpackage URL Builder
 */

declare(strict_types=1);

namespace AppUtils\URLBuilder;

use AppUtils\BaseException;

/**
 * @package Application Utils
 * @subpackage URL Builder
 */
class URLBuilderException extends BaseException
{
    public const ERROR_INVALID_HOST = 169601;
}
