<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\Parser\BaseURLValidator
 */

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser;

use AppUtils\URLInfo\URLInfoTrait;
use AppUtils\URLInfo\URIParser;

/**
 * Base class for URL validation classes.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseURLValidator
{
    use URLInfoTrait;

    protected URIParser $parser;

    public function __construct(URIParser $parser)
    {
        $this->parser = $parser;
        $this->info = $parser->getInfo();
    }

    abstract public function validate() : bool;
}
