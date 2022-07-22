<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 */

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser;

use AppUtils\URLInfo;
use AppUtils\URLInfo\URIParser;
use AppUtils\URLInfo\URLInfoTrait;

/**
 * Base class for URL type detectors.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseURLTypeDetector
{
    use URLInfoTrait;

    protected URIParser $parser;

    /**
     * @param URIParser $parser
     */
    public function __construct(URIParser $parser)
    {
        $this->parser = $parser;
        $this->info = $parser->getInfo();
    }

    abstract public function detect() : bool;
}
