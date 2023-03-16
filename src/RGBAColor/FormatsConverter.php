<?php
/**
 * File containing the class {@see FormatsConverter}.
 *
 * @see FormatsConverter
 *@subpackage RGBAColor
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\FormatsConverter\HEXParser;
use function AppUtils\parseVariable;

/**
 * The converter static class is used to convert between color
 * information formats.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FormatsConverter
{
    public const ERROR_INVALID_COLOR_ARRAY = 99701;

    private static ?HEXParser $hexParser = null;

    /**
     * Converts the color to a HEX color value. This is either
     * a RRGGBB or RRGGBBAA string, depending on whether there
     * is an alpha channel value.
     *
     * NOTE: The HEX letters are always uppercase.
     *
     * @param RGBAColor $color
     * @return string
     */
    public static function color2HEX(RGBAColor $color) : string
    {
        $hex =
            UnitsConverter::int2hex($color->getRed()->get8Bit()) .
            UnitsConverter::int2hex($color->getGreen()->get8Bit()) .
            UnitsConverter::int2hex($color->getBlue()->get8Bit());

        if($color->hasTransparency())
        {
            $hex .= UnitsConverter::int2hex($color->getAlpha()->get8Bit());
        }

        return $hex;
    }

    /**
     * Converts the color to a CSS `rgb()` or `rgba()` value.
     *
     * @param RGBAColor $color
     * @return string
     */
    public static function color2CSS(RGBAColor $color) : string
    {
        if($color->hasTransparency())
        {
            return sprintf(
                'rgba(%s, %s, %s, %s)',
                $color->getRed()->get8Bit(),
                $color->getGreen()->get8Bit(),
                $color->getBlue()->get8Bit(),
                $color->getAlpha()->getAlpha()
            );
        }

        return sprintf(
            'rgb(%s, %s, %s)',
            $color->getRed()->get8Bit(),
            $color->getGreen()->get8Bit(),
            $color->getBlue()->get8Bit()
        );
    }

    public static function color2array(RGBAColor $color) : ArrayConverter
    {
        return new ArrayConverter($color);
    }

    /**
     * Checks if the array is a valid color array with
     * all expected color keys present. The `alpha` key
     * is optional. If it's not valid, throws an exception.
     *
     * @param array<string|int,int|float> $color
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_ARRAY
     */
    public static function requireValidColorArray(array $color) : void
    {
        if(self::isColorArray($color))
        {
            return;
        }

        throw new ColorException(
            'Not a valid color array.',
            sprintf(
                'The color array is in the wrong format, or is missing required keys. '.
                'Given: '.PHP_EOL.
                '%s',
                parseVariable($color)->toString()
            ),
            RGBAColor::ERROR_INVALID_COLOR_ARRAY
        );
    }

    /**
     * Checks whether the specified array contains all required
     * color keys.
     *
     * @param array<string|int,int|float> $color
     * @return bool
     */
    public static function isColorArray(array $color) : bool
    {
        $keys = array(
            RGBAColor::CHANNEL_RED,
            RGBAColor::CHANNEL_GREEN,
            RGBAColor::CHANNEL_BLUE
        );

        foreach($keys as $key)
        {
            if(!isset($color[$key]))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Human-readable label of the color. Automatically
     * switches between RGBA and RGB depending on whether
     * the color has any transparency.
     *
     * @param RGBAColor $color
     * @return string
     */
    public static function color2readable(RGBAColor $color) : string
    {
        if($color->hasTransparency())
        {
            return sprintf(
                'RGBA(%s %s %s %s)',
                $color->getRed()->get8Bit(),
                $color->getGreen()->get8Bit(),
                $color->getBlue()->get8Bit(),
                $color->getAlpha()->get8Bit()
            );
        }

        return sprintf(
            'RGB(%s %s %s)',
            $color->getRed()->get8Bit(),
            $color->getGreen()->get8Bit(),
            $color->getBlue()->get8Bit()
        );
    }

    /**
     * Parses a HEX color value, and converts it to
     * an RGBA color array.
     *
     * Examples:
     *
     * <pre>
     * $color = RGBAColor_Utilities::parseHexColor('CCC');
     * $color = RGBAColor_Utilities::parseHexColor('CCDDEE');
     * $color = RGBAColor_Utilities::parseHexColor('CCDDEEFA');
     * </pre>
     *
     * @param string $hex
     * @param string $name
     * @return RGBAColor
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_HEX_LENGTH
     */
    public static function hex2color(string $hex, string $name='') : RGBAColor
    {
        if(!isset(self::$hexParser))
        {
            self::$hexParser = new HEXParser();
        }

        return self::$hexParser->parse($hex, $name);
    }

    /**
     * @var array<int,array{key:string,mandatory:bool}>
     */
    private static array $keys = array(
        array(
            'key' => RGBAColor::CHANNEL_RED,
            'mandatory' => true
        ),
        array(
            'key' => RGBAColor::CHANNEL_GREEN,
            'mandatory' => true
        ),
        array(
            'key' => RGBAColor::CHANNEL_BLUE,
            'mandatory' => true
        ),
        array(
            'key' => RGBAColor::CHANNEL_ALPHA,
            'mandatory' => false
        )
    );

    /**
     * Converts a color array to an associative color
     * array. Works with indexed color arrays, as well
     * as arrays that are already associative.
     *
     * Expects the color values to always be in the same order:
     *
     * - red
     * - green
     * - blue
     * - alpha (optional)
     *
     * @param array<int|string,int|float> $color
     * @return array<string,int|float>
     *
     * @throws ColorException
     * @see FormatsConverter::ERROR_INVALID_COLOR_ARRAY
     */
    public static function array2associative(array $color) : array
    {
        // If one associative key is present, we assume
        // that the color array is already correct.
        if(isset($color[RGBAColor::CHANNEL_RED]))
        {
            return $color;
        }

        $values = array_values($color);
        $result = array();

        foreach(self::$keys as $idx => $def)
        {
            if(isset($values[$idx]))
            {
                $result[$def['key']] = $values[$idx];
                continue;
            }

            if(!$def['mandatory'])
            {
                continue;
            }

            throw new ColorException(
                'Invalid color array',
                sprintf(
                    'The value for [%s] is missing at index [%s] in the source array, and it is mandatory.',
                    $def['key'],
                    $idx
                ),
                self::ERROR_INVALID_COLOR_ARRAY
            );
        }

        return $result;
    }

    /**
     * Converts an RGB color value to HSV.
     *
     * @param int $red 0-255
     * @param int $green 0-255
     * @param int $blue 0-255
     * @return array{hue:float,saturation:float,brightness:float} HSV values: 0-360, 0-100, 0-100
     */
    public static function rgb2hsv(int $red, int $green, int $blue) : array
    {
        // Convert the RGB byte-values to percentages
        $R = ($red / 255);
        $G = ($green / 255);
        $B = ($blue / 255);

        // Calculate a few basic values, the maximum value of R,G,B, the
        //   minimum value, and the difference of the two (chroma).
        $maxRGB = max($R, $G, $B);
        $minRGB = min($R, $G, $B);
        $chroma = $maxRGB - $minRGB;

        // Value (also called Brightness) is the easiest component to calculate,
        //   and is simply the highest value among the R,G,B components.
        // We multiply by 100 to turn the decimal into a readable percent value.
        $computedV = 100 * $maxRGB;

        // Special case if hueless (equal parts RGB make black, white, or grays)
        // Note that Hue is technically undefined when chroma is zero, as
        //   attempting to calculate it would cause division by zero (see
        //   below), so most applications simply substitute a Hue of zero.
        // Saturation will always be zero in this case, see below for details.
        if ($chroma === 0)
        {
            return array(
                'hue' => 0.0,
                'saturation' => 0.0,
                'brightness' => $computedV
            );
        }

        // Saturation is also simple to compute, and is simply the chroma
        //   over the Value (or Brightness)
        // Again, multiplied by 100 to get a percentage.
        $computedS = 100 * ($chroma / $maxRGB);

        // Calculate Hue component
        // Hue is calculated on the "chromacity plane", which is represented
        //   as a 2D hexagon, divided into six 60-degree sectors. We calculate
        //   the bisecting angle as a value 0 <= x < 6, that represents which
        //   portion of which sector the line falls on.
        if ($R === $minRGB)
        {
            $h = 3 - (($G - $B) / $chroma);
        }
        elseif ($B === $minRGB)
        {
            $h = 1 - (($R - $G) / $chroma);
        }
        else
        { // $G == $minRGB
            $h = 5 - (($B - $R) / $chroma);
        }

        // After we have the sector position, we multiply it by the size of
        //   each sector's arc (60 degrees) to obtain the angle in degrees.
        $computedH = 60 * $h;

        return array(
            'hue' => $computedH,
            'saturation' => $computedS,
            'brightness' => $computedV
        );
    }

    /**
     * Converts an HSV value to RGB.
     *
     * @param float $hue 0-360
     * @param float $saturation 0-100
     * @param float $brightness 0-100
     * @return array{red:int,green:int,blue:int} 0-255
     * @link https://gist.github.com/vkbo/2323023
     */
    public static function hsv2rgb(float $hue, float $saturation, float $brightness) : array
    {

        if($hue < 0) {  $hue = 0.0; } // Hue:
        if($hue > 360) { $hue = 360.0; } // 0.0 to 360.0
        if($saturation < 0) { $saturation = 0.0; } // Saturation:
        if($saturation > 100) { $saturation = 100.0; } // 0.0 to 100.0
        if($brightness < 0) { $brightness = 0.0; }  // Brightness:
        if($brightness > 100) { $brightness = 100.0; } // 0.0 to 100.0

        $dS = $saturation/100.0; // Saturation: 0.0 to 1.0
        $dV = $brightness/100.0; // Brightness: 0.0 to 1.0
        $dC = $dV*$dS; // Chroma: 0.0 to 1.0
        $dH = $hue/60.0; // H-Prime: 0.0 to 6.0
        $dT = $dH; // Temp variable

        while($dT >= 2.0) { $dT -= 2.0; } // php modulus does not work with float
        $dX = $dC*(1-abs($dT-1)); // as used in the Wikipedia link

        switch(floor($dH)) {
            case 0:
                $dR = $dC; $dG = $dX; $dB = 0.0; break;
            case 1:
                $dR = $dX; $dG = $dC; $dB = 0.0; break;
            case 2:
                $dR = 0.0; $dG = $dC; $dB = $dX; break;
            case 3:
                $dR = 0.0; $dG = $dX; $dB = $dC; break;
            case 4:
                $dR = $dX; $dG = 0.0; $dB = $dC; break;
            case 5:
                $dR = $dC; $dG = 0.0; $dB = $dX; break;
            default:
                $dR = 0.0; $dG = 0.0; $dB = 0.0; break;
        }

        $dM  = $dV - $dC;
        $dR += $dM; $dG += $dM; $dB += $dM;
        $dR *= 255; $dG *= 255; $dB *= 255;

        return array(
            'red' => (int)round($dR),
            'green' => (int)round($dG),
            'blue' => (int)round($dB)
        );
    }
}
