<?php

declare(strict_types=1);

namespace AppUtils;

class NumberInfo_Immutable extends NumberInfo
{
    /**
     * @param NumberInfo|float|int|string|NULL $value
     * @return NumberInfo_Immutable
     */
    public function setValue($value)
    {
        $number = parseNumber($value, true);

        if($number->getNumber() === $this->getNumber() && $number->hasUnits() === $this->hasUnits())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }

    /**
     * @param NumberInfo|float|int|string|NULL $number
     * @return NumberInfo_Immutable
     */
    public function setNumber($number)
    {
        $number = parseNumber($number, true);

        if($number->getNumber() === $this->getNumber() && $number->getUnits() === $this->getUnits())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }

    /**
     * @param NumberInfo|float|int|string|null $value
     * @return NumberInfo_Immutable
     */
    public function add($value)
    {
        $number = parseNumber($this, true);
        $number->add($value);

        if($number->getNumber() === $this->getNumber())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }

    /**
     * @param NumberInfo|float|int|string|NULL $value
     * @return NumberInfo_Immutable
     */
    public function subtract($value)
    {
        $number = parseNumber($this, true);
        $number->subtract($value);

        if($number->getNumber() === $this->getNumber())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }

    /**
     * @param float $percent
     * @return NumberInfo_Immutable
     */
    public function subtractPercent(float $percent)
    {
        $number = parseNumber($this, true);
        $number->subtractPercent($percent);

        if($number->getNumber() === $this->getNumber())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }

    /**
     * @param float $percent
     * @return NumberInfo_Immutable
     */
    public function addPercent(float $percent)
    {
        $number = parseNumber($this, true);
        $number->addPercent($percent);

        if($number->getNumber() === $this->getNumber())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }

    /**
     * @return NumberInfo_Immutable
     */
    public function floorEven() : NumberInfo
    {
        $number = parseNumber($this, true);
        $number->floorEven();

        if($number->getNumber() === $this->getNumber())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }

    /**
     * @return NumberInfo_Immutable
     */
    public function ceilEven()
    {
        $number = parseNumber($this, true);
        $number->ceilEven();

        if($number->getNumber() === $this->getNumber())
        {
            return $this;
        }

        return parseNumberImmutable($number);
    }
}
