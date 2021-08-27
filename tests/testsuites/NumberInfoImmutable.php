<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use function AppUtils\parseNumberImmutable;

final class NumberInfoImmutableTest extends TestCase
{
    public function test_create() : void
    {
        $number = parseNumberImmutable(42);

        $this->assertNotSame(
            $number->getInstanceID(),
            parseNumberImmutable($number)->getInstanceID()
        );
    }

    public function test_setNumber_sameInstance() : void
    {
        $number = parseNumberImmutable(42);
        $result = $number->setNumber(42);

        $this->assertSame($number->getInstanceID(), $result->getInstanceID());

        $number = parseNumberImmutable(42);
        $result = $number->setNumber('42px');

        $this->assertSame($number->getInstanceID(), $result->getInstanceID());

        $number = parseNumberImmutable('42%');
        $result = $number->setNumber('42%');

        $this->assertSame($number->getInstanceID(), $result->getInstanceID());
    }

    public function test_setNumber_unitsMismatch() : void
    {
        $number = parseNumberImmutable(42);
        $result = $number->setNumber('42%');

        $this->assertNotSame($number->getInstanceID(), $result->getInstanceID());
    }

    public function test_setValue_sameInstance() : void
    {
        $number = parseNumberImmutable('42em');
        $result = $number->setNumber('42em');

        $this->assertSame($number->getInstanceID(), $result->getInstanceID());
    }

    public function test_add() : void
    {
        $number = parseNumberImmutable(42);
        $added = $number->add(10);

        $this->assertSame(42, $number->getNumber());
        $this->assertSame(52, $added->getNumber());
        $this->assertNotSame($number->getInstanceID(), $added->getInstanceID());
    }

    public function test_addPercent() : void
    {
        $number = parseNumberImmutable(100);
        $added = $number->addPercent(12);

        $this->assertSame(100, $number->getNumber());
        $this->assertSame(112, $added->getNumber());
        $this->assertNotSame($number->getInstanceID(), $added->getInstanceID());
    }

    public function test_subtractPercent() : void
    {
        $number = parseNumberImmutable(100);
        $subtracted = $number->subtractPercent(12);

        $this->assertSame(100, $number->getNumber());
        $this->assertSame(88, $subtracted->getNumber());
        $this->assertNotSame($number->getInstanceID(), $subtracted->getInstanceID());
    }

    public function test_ceilEven() : void
    {
        $number = parseNumberImmutable(41);
        $result = $number->ceilEven();

        $this->assertSame(41, $number->getNumber());
        $this->assertSame(42, $result->getNumber());
        $this->assertNotSame($number->getInstanceID(), $result->getInstanceID());
    }

    public function test_floorEven() : void
    {
        $number = parseNumberImmutable(43);
        $result = $number->floorEven();

        $this->assertSame(43, $number->getNumber());
        $this->assertSame(42, $result->getNumber());
        $this->assertNotSame($number->getInstanceID(), $result->getInstanceID());
    }

    public function test_setNumber() : void
    {
        $number = parseNumberImmutable(41);
        $result = $number->setNumber(42);

        $this->assertSame(41, $number->getNumber());
        $this->assertSame(42, $result->getNumber());
        $this->assertNotSame($number->getInstanceID(), $result->getInstanceID());
    }

    public function test_setValue() : void
    {
        $number = parseNumberImmutable(41);
        $result = $number->setValue('42px');

        $this->assertSame(41, $number->getNumber());
        $this->assertSame(42, $result->getNumber());
        $this->assertNotSame($number->getInstanceID(), $result->getInstanceID());
    }

    public function test_subtract() : void
    {
        $number = parseNumberImmutable(43);
        $result = $number->subtract(1);

        $this->assertSame(43, $number->getNumber());
        $this->assertSame(42, $result->getNumber());
        $this->assertNotSame($number->getInstanceID(), $result->getInstanceID());
    }
}
