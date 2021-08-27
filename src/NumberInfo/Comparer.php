<?php
/**
 * File containing the class {@see NumberInfo_Comparer}.
 *
 * @package Application Utils
 * @subpackage NumberInfo
 * @see NumberInfo_Comparer
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Comparison tool for numbers using {@see NumberInfo} instances.
 *
 * @package Application Utils
 * @subpackage NumberInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class NumberInfo_Comparer
{
    /**
     * @var NumberInfo
     */
    private $a;

    /**
     * @var NumberInfo
     */
    private $b;

    /**
     * @var bool
     */
    private $valid;

    public function __construct(NumberInfo $a, NumberInfo $b)
    {
        $this->a = $a;
        $this->b = $b;

        $this->valid = $this->validate();
    }

    /**
     * Ensures that the two numbers can be validated.
     *
     * Invalid numbers are:
     *
     * - Numbers with different units
     * - Empty numbers
     *
     * @return bool
     */
    private function validate() : bool
    {
        if($this->a->getUnits() !== $this->b->getUnits())
        {
            return false;
        }

        if($this->a->isEmpty() || $this->b->isEmpty())
        {
            return false;
        }

        return true;
    }

    public function isBiggerThan() : bool
    {
        if($this->valid === false)
        {
            return false;
        }

        return $this->a->getNumber() > $this->b->getNumber();
    }

    public function isBiggerEqual() : bool
    {
        if($this->valid === false)
        {
            return false;
        }

        return $this->a->getNumber() >= $this->b->getNumber();
    }

    public function isSmallerThan() : bool
    {
        if($this->valid === false)
        {
            return false;
        }

        return $this->a->getNumber() < $this->b->getNumber();
    }

    public function isSmallerEqual() : bool
    {
        if($this->valid === false)
        {
            return false;
        }

        return $this->a->getNumber() <= $this->b->getNumber();
    }

    public function isEqual() : bool
    {
        if($this->valid === false)
        {
            return false;
        }

        return $this->a->getNumber() === $this->b->getNumber();
    }
}
