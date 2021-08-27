<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use function AppUtils\parseNumber;

final class NumberInfoTest extends TestCase
{
    public function testParse() : void
    {
        $tests = array(
            array('value' => '', 'label' => 'Empty string', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '0', 'label' => 'String zero', 'units' => 'px', 'number' => 0, 'css' => '0'),
            array('value' => '1', 'label' => 'String one', 'units' => 'px', 'number' => 1, 'css' => '1px'),
            array('value' => 1, 'label' => 'Numeric one', 'units' => 'px', 'number' => 1, 'css' => '1px'),
            array('value' => 8.7, 'label' => 'Decimal value', 'units' => 'px', 'number' => 8, 'css' => '8px'),
            array('value' => 8.1, 'label' => 'Decimal value', 'units' => 'px', 'number' => 8, 'css' => '8px'),
            array('value' => '1,89', 'label' => 'String comma number', 'units' => 'px', 'number' => 1, 'css' => '1px'),
            array('value' => '10.42', 'label' => 'String dot number', 'units' => 'px', 'number' => 10, 'css' => '10px'),
            array('value' => null, 'label' => 'NULL value', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => false, 'label' => 'Boolean false', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => 'blabla', 'label' => 'String', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '1500', 'label' => 'String integer', 'units' => 'px', 'number' => 1500, 'css' => '1500px'),
            
            array('value' => '50%', 'label' => 'Percentage', 'units' => '%', 'number' => 50, 'css' => '50%'),
            array('value' => '15 px', 'label' => 'Pixel string with space', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15       px', 'label' => 'Pixel string with many spaces', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15px', 'label' => 'Pixel string without spaces', 'units' => 'px', 'number' => 15, 'css' => '15px'),
            array('value' => '15,45em', 'label' => 'EM float value with comma', 'units' => 'em', 'number' => 15.45, 'css' => '15.45em'),
            array('value' => '15.45em', 'label' => 'EM float value with dot', 'units' => 'em', 'number' => 15.45, 'css' => '15.45em'),
            array('value' => 'px', 'label' => 'Solo px units without number', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '0px', 'label' => 'Zero with px units', 'units' => 'px', 'number' => 0, 'css' => '0'),
            array('value' => 'blapx', 'label' => 'String with px units', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => '    bla  px    ', 'label' => 'String with px units and spaces', 'units' => '', 'number' => 0, 'css' => ''),
            
            array('value' => array(), 'label' => 'Empty array', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => array(0), 'label' => 'Array', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => imagecreate(10, 10), 'label' => 'Resource', 'units' => '', 'number' => 0, 'css' => ''),
            array('value' => new DateTime(), 'label' => 'Object', 'units' => '', 'number' => 0, 'css' => ''),
        );
        
        $number = parseNumber(0);
        
        foreach($tests as $test)
        { 
            $number->setValue($test['value']);
            
            $this->assertSame($test['units'], $number->getUnits(), 'Units check: '.$test['label']);
            $this->assertSame($test['number'], $number->getNumber(), 'Number check: '.$test['label']);
            $this->assertSame($test['css'], $number->toCSS(), 'CSS check: '.$test['label']);
        }
    }

    public function test_isZero() : void
    {
        $this->assertTrue(parseNumber('0')->isZero(), 'String 0');
        $this->assertTrue(parseNumber(0)->isZero(), 'Int 0');
        $this->assertTrue(parseNumber(0.0)->isZero(), 'Float 0');

        $this->assertFalse(parseNumber('')->isZero(), 'Empty string');
        $this->assertFalse(parseNumber(null)->isZero(), 'NULL value');
    }

    public function test_isEmpty() : void
    {
        $this->assertFalse(parseNumber('0')->isEmpty(), 'String 0');
        $this->assertFalse(parseNumber(0)->isEmpty(), 'Int 0');
        $this->assertFalse(parseNumber(0.0)->isEmpty(), 'Float 0');

        $this->assertTrue(parseNumber('Invalid string')->isEmpty(), 'Invalid number');
        $this->assertTrue(parseNumber('')->isEmpty(), 'Empty string');
        $this->assertTrue(parseNumber(null)->isEmpty(), 'NULL value');
        $this->assertTrue(parseNumber('    \t \n')->isEmpty(), 'Whitespace only');
    }

    public function test_hasDecimals() : void
    {
        $this->assertFalse(parseNumber('0')->hasDecimals(), 'String 0');
        $this->assertFalse(parseNumber(0)->hasDecimals(), 'Int 0');
        $this->assertFalse(parseNumber(0.0)->hasDecimals(), 'Numeric 0.0');
        $this->assertFalse(parseNumber('15,00em')->hasDecimals(), 'String 15,00em');
        $this->assertFalse(parseNumber(array())->hasDecimals(), 'Empty array');
        $this->assertFalse(parseNumber(0.1)->hasDecimals(), 'Numeric 0.1 (unitless=pixels, cannot have decimals)');

        $this->assertTrue(parseNumber('55.20%')->hasDecimals(), 'String percentage');
        $this->assertTrue(parseNumber('0.1em')->hasDecimals(), 'Numeric 0.1em');
        $this->assertTrue(parseNumber('1.1em')->hasDecimals(), 'String 1.1');
        $this->assertTrue(parseNumber('15,21em')->hasDecimals(), 'String 15,2em');

        $this->assertTrue(parseNumber('33.33em')->hasDecimals(), 'String 33.33em');
    }

    public function test_isEven() : void
    {
        $this->assertFalse(parseNumber('')->isEven(), 'Empty string');
        $this->assertFalse(parseNumber(null)->isEven(), 'NULL value');
        $this->assertFalse(parseNumber(1)->isEven(), 'Int 1');
        $this->assertFalse(parseNumber('1.45em')->isEven(), 'String 1.45em');

        $this->assertTrue(parseNumber(0)->isEven(), 'Int zero');
        $this->assertTrue(parseNumber('0')->isEven(), 'String zero');
        $this->assertTrue(parseNumber('2')->isEven(), 'String two');
        $this->assertTrue(parseNumber('2.45em')->isEven(), 'String 2.45em');
    }

    public function test_hasUnits() : void
    {
        $this->assertFalse(parseNumber('')->hasUnits(), 'Empty string');
        $this->assertFalse(parseNumber(null)->hasUnits(), 'NULL value');
        $this->assertFalse(parseNumber(15)->hasUnits(), 'Int 15');
        $this->assertFalse(parseNumber('15')->hasUnits(), 'String 15');

        $this->assertTrue(parseNumber('15%')->hasUnits(), 'String 15%');
        $this->assertTrue(parseNumber('1.45em')->hasUnits(), 'String 1.45em');
    }

    public function test_setNumber() : void
    {
        $this->assertSame(0.8, parseNumber('0.2em')->setNumber('0.8em')->getNumber());
        $this->assertSame(5, parseNumber(0)->setNumber(5)->getNumber());
    }

    /**
     * It must not be possible to change a number's
     * units by setting a number with different units.
     * It must simply be ignored.
     */
    public function test_changeUnits() : void
    {
        $number = parseNumber(0)->setNumber('5em');

        $this->assertSame(0, $number->getNumber());
        $this->assertSame('px', $number->getUnits());
    }

    public function test_floorEven() : void
    {
        $tests = array(
            array(
                'label' => 'Even integer',
                'number' => 44,
                'expected' => 44
            ),
            array(
                'label' => 'Uneven integer',
                'number' => 15,
                'expected' => 14
            ),
            array(
                'label' => 'Decimal even',
                'number' => 42.4,
                'expected' => 42
            ),
            array(
                'label' => 'Decimal uneven',
                'number' => 87.45,
                'expected' => 86
            ),
            array(
                'label' => 'Decimal high even',
                'number' => 50.99,
                'expected' => 50
            ),
            array(
                'label' => 'Decimal high uneven',
                'number' => 31.999,
                'expected' => 30
            )
        );

        foreach ($tests as $test)
        {
            $parsed = parseNumber($test['number']);
            $parsed->floorEven();

            $this->assertSame($test['expected'], $parsed->getNumber(), $test['label']);
        }
    }

    /**
     * Flooring a number must update both the number
     * and the value. The value must also respect the
     * units (or lack thereof) when updating the value.
     */
    public function test_floorEven_updateValue() : void
    {
        $number = parseNumber(41);
        $number->ceilEven();

        $this->assertSame(42, $number->getNumber());
        $this->assertSame(42, $number->getValue());

        $number = parseNumber('41em');
        $number->ceilEven();

        $this->assertSame(42, $number->getNumber());
        $this->assertSame('42em', $number->getValue());
    }

    public function test_ceilEven() : void
    {
        $tests = array(
            array(
                'label' => 'Even integer',
                'number' => 44,
                'expected' => 44
            ),
            array(
                'label' => 'Uneven integer',
                'number' => 15,
                'expected' => 16
            ),
            array(
                'label' => 'Decimal even',
                'number' => 42.4,
                'expected' => 42
            ),
            array(
                'label' => 'Decimal uneven',
                'number' => 87.45,
                'expected' => 88
            ),
            array(
                'label' => 'Decimal high even',
                'number' => 50.99,
                'expected' => 50
            ),
            array(
                'label' => 'Decimal high uneven',
                'number' => 31.999,
                'expected' => 32
            ),
            array(
                'label' => 'Integer uneven',
                'number' => 41,
                'expected' => 42
            )
        );

        foreach ($tests as $test)
        {
            $parsed = parseNumber($test['number']);
            $parsed->ceilEven();

            $this->assertSame($test['expected'], $parsed->getNumber(), $test['label']);
        }
    }

    /**
     * Applying ceiling to a number must update both the number
     * and the value. The value must also respect the
     * units (or lack thereof) when updating the value.
     */
    public function test_ceilEven_updateValue() : void
    {
        $number = parseNumber(43);
        $number->floorEven();

        $this->assertSame(42, $number->getNumber());
        $this->assertSame(42, $number->getValue());

        $number = parseNumber('43em');
        $number->floorEven();

        $this->assertSame(42, $number->getNumber());
        $this->assertSame('42em', $number->getValue());
    }

    public function test_isBiggerEqual() : void
    {
        $tests = array(
            array(
                'label' => 'One',
                'number' => 1,
                'compareWith' => 1,
                'expected' => true
            ),
            array(
                'label' => 'Zero',
                'number' => 0,
                'compareWith' => 0,
                'expected' => true
            ),
            array(
                'label' => 'Decimals, slight change',
                'number' => '14.1%',
                'compareWith' => '14.11%',
                'expected' => false
            ),
            array(
                'label' => 'Decimals, same number but different units',
                'number' => '33.33rem',
                'compareWith' => '33.33em',
                'expected' => false
            ),
            array(
                'label' => 'Null and zero',
                'number' => null,
                'compareWith' => 0,
                'expected' => false
            ),
            array(
                'label' => 'Null and null',
                'number' => null,
                'compareWith' => null,
                'expected' => false
            ),
            array(
                'label' => 'Negative and positive same number',
                'number' => 50,
                'compareWith' => -50,
                'expected' => true
            ),
            array(
                'label' => 'Decimals, high precision',
                'number' => '42.424242%',
                'compareWith' => '42.424242%',
                'expected' => true
            ),
            array(
                'label' => 'Pixel value',
                'number' => 78,
                'compareWith' => parseNumber('78px'),
                'expected' => true
            ),
            array(
                'label' => 'EM value',
                'number' => '55.1em',
                'compareWith' => parseNumber('55em'),
                'expected' => true
            ),
            array(
                'label' => 'Two instances',
                'number' => parseNumber('1147'),
                'compareWith' => 1147,
                'expected' => true
            )
        );

        foreach($tests as $test)
        {
            $parsed = parseNumber($test['number']);
            $result = $parsed->isBiggerEqual($test['compareWith']);

            $this->assertSame($test['expected'], $result, $test['label']);
        }
    }

    public function test_getNumber() : void
    {
        $tests = array(
            array(
                'label' => 'NULL',
                'number' => null,
                'expected' => 0
            ),
            array(
                'label' => 'Integer zero',
                'number' => 0,
                'expected' => 0
            ),
            array(
                'label' => 'Negative number',
                'number' => -42,
                'expected' => -42
            ),
            array(
                'label' => 'Decimals without units',
                'number' => 14.45,
                'expected' => 14
            ),
            array(
                'label' => 'Pixel value with decimals',
                'number' => '14.45px',
                'expected' => 14
            ),
            array(
                'label' => 'EM value with decimals',
                'number' => '14.45em',
                'expected' => 14.45
            ),
            array(
                'label' => 'REM value with decimals',
                'number' => '33.33rem',
                'expected' => 33.33
            ),
            array(
                'label' => 'Negative REM value with decimals',
                'number' => '-33.33rem',
                'expected' => -33.33
            )
        );

        foreach($tests as $test)
        {
            $parsed = parseNumber($test['number']);

            $this->assertSame(
                $test['expected'],
                $parsed->getNumber(),
                $test['label']
            );
        }
    }

    public function test_addPercent() : void
    {
        $number = parseNumber(100);
        $number->addPercent(12);

        $this->assertSame(112, $number->getNumber());
        $this->assertSame(112, $number->getValue());
    }

    public function test_addPercent_keepUnits() : void
    {
        $number = parseNumber('100em');
        $number->addPercent(12);

        $this->assertSame(112, $number->getNumber());
        $this->assertSame('112em', $number->getValue());
    }

    public function test_subtractPercent() : void
    {
        $number = parseNumber(100);
        $number->subtractPercent(12);

        $this->assertSame(88, $number->getNumber());
        $this->assertSame(88, $number->getValue());
    }

    public function test_subtractPercent_keepUnits() : void
    {
        $number = parseNumber('100em');
        $number->subtractPercent(12);

        $this->assertSame(88, $number->getNumber());
        $this->assertSame('88em', $number->getValue());
    }

    public function test_isPixels() : void
    {
        $this->assertTrue(parseNumber(42)->isPixels());
        $this->assertTrue(parseNumber('42px')->isPixels());
        $this->assertFalse(parseNumber('42%')->isPixels());
    }

    public function test_isPercent() : void
    {
        $this->assertFalse(parseNumber(42)->isPercent());
        $this->assertFalse(parseNumber('42em')->isPercent());
        $this->assertTrue(parseNumber('42%')->isPercent());
    }

    public function getValue() : void
    {
        $this->assertSame(null, parseNumber(null)->getValue());
        $this->assertSame(0, parseNumber(0)->getValue());
        $this->assertSame(1.5, parseNumber(1.5)->getValue());
        $this->assertSame('42%', parseNumber('42%')->getValue());
        $this->assertSame(42, parseNumber(parseNumber(42))->getValue());
    }

    public function test_isUnitDecimal() : void
    {
        $this->assertTrue(parseNumber('42%')->isUnitDecimal());
        $this->assertTrue(parseNumber('42em')->isUnitDecimal());
        $this->assertTrue(parseNumber('42rem')->isUnitDecimal());

        $this->assertFalse(parseNumber(42)->isUnitDecimal());
        $this->assertFalse(parseNumber('42px')->isUnitDecimal());
    }

    public function test_isUnitInteger() : void
    {
        $this->assertFalse(parseNumber('42%')->isUnitInteger());
        $this->assertFalse(parseNumber('42em')->isUnitInteger());
        $this->assertFalse(parseNumber('42rem')->isUnitInteger());

        $this->assertTrue(parseNumber(42)->isUnitInteger());
        $this->assertTrue(parseNumber('42px')->isUnitInteger());
    }

    public function test_cachingByNumber() : void
    {
        $number = parseNumber(42);

        $this->assertSame(
            $number->getInstanceID(),
            parseNumber($number)->getInstanceID()
        );
    }

    public function test_bypassCaching() : void
    {
        $number = parseNumber(42);

        $this->assertNotSame(
            $number->getInstanceID(),
            parseNumber($number, true)->getInstanceID()
        );
    }

    public function test_add() : void
    {
        $number = parseNumber(41);
        $number->add(1);

        $this->assertSame(42, $number->getNumber());
        $this->assertSame(42, $number->getValue());
    }

    public function test_add_keepUnits() : void
    {
        $number = parseNumber('41%');
        $number->add(1);

        $this->assertSame(42, $number->getNumber());
        $this->assertSame('42%', $number->getValue());
    }

    public function test_subtract() : void
    {
        $number = parseNumber(43);
        $number->subtract(1);

        $this->assertSame(42, $number->getNumber());
        $this->assertSame(42, $number->getValue());
    }

    public function test_subtract_keepUnits() : void
    {
        $number = parseNumber('43%');
        $number->subtract(1);

        $this->assertSame(42, $number->getNumber());
        $this->assertSame('42%', $number->getValue());
    }
}
