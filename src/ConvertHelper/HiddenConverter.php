<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_HiddenConverter} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_HiddenConverter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Can replace any hidden characters (like whitespace or control characters)
 * with visible, easily identifiable strings for easy debugging.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_HiddenConverter
{
    const CHARS_WHITESPACE = 'whitespace';
    const CHARS_CONTROL = 'control';

    /**
     * @var array<string,array<string,string>>
     */
    protected $characters = array(
        'whitespace' => array(
            "\t" => '[TAB]',
            "\n" => '[LF]',
            "\r" => '[CR]',
            " " => '[SPACE]'
        ),
        'control' => array(
            "\x00" => '[NUL]', // Null
            "\x01" => '[SOH]', // 1 Start of heading
            "\x02" => '[STX]', // 2 Start of text
            "\x03" => '[ETX]', // 3 End of text
            "\x04" => '[EOT]', // 4 End of transmission
            "\x05" => '[ENQ]', // 5 Enquiry
            "\x06" => '[ACK]', // 6 Acknowledge
            "\x07" => '[BEL]', // 7 Bell
            "\x08" => '[BS]', // 8 Backspace
            //"\x09" => '[HT]', // 9 Horizontal tabulation (Already in whitespace)
            //"\x0A" => '[LF]', // 10 Line feed (Already in whitespace)
            "\x0B" => '[VT]', // 11 Vertical tabulation
            "\x0C" => '[FF]', // 12 Form feed
            //"\x0D" => '[CR]', // 13 Carriage return (Already in whitespace)
            "\x0E" => '[SO]', // 14 Shift out
            "\x0F" => '[SI]', // 15 Shift in
            "\x10" => '[DLE]', // 16 Data link escape
            "\x11" => '[DC1]', // 17 Device control 1
            "\x12" => '[DC2]', // 18 Device control 2
            "\x13" => '[DC3]', // 19 Device control 3
            "\x14" => '[DC4]', // 20 Device control 4
            "\x15" => '[NAK]', // 21 Negative acknowledge
            "\x16" => '[SYN]', // 22 Synchronous idle
            "\x17" => '[ETB]', // 23 End of transmission block
            "\x18" => '[CAN]', // 24 Cancel
            "\x19" => '[EM]', // 25 End of medium
            "\x1A" => '[SUB]', // 26 Substitute
            "\x1B" => '[ESC]', // 27 Escape
            "\x1C" => '[FS]', // 28 File separator
            "\x1D" => '[GS]', // 29 Group Separator
            "\x1E" => '[RS]', // 30 Record Separator
            "\x1F" => '[US]', // 31 Unit Separator
            "\x7F" => '[DEL]' // 127 Delete
        )
    );
    
   /**
    * @var string[]
    */
    protected $selected = array();
    
    public function convert(string $string) : string
    {
        $chars = $this->resolveSelection();
        
        return str_replace(array_keys($chars), array_values($chars), $string);
    }
    
   /**
    * Selects a character set to replace. Can be called
    * several times to add additional sets to the collection.
    * 
    * @param string $type See the <code>CHAR_XXX</code> constants.
    * @return ConvertHelper_HiddenConverter
    * 
    * @see ConvertHelper_HiddenConverter::CHARS_CONTROL
    * @see ConvertHelper_HiddenConverter::CHARS_WHITESPACE
    */
    public function selectCharacters(string $type) : ConvertHelper_HiddenConverter
    {
        if(!in_array($type, $this->selected)) {
            $this->selected[] = $type;
        }
        
        return $this;
    }
    
   /**
    * Resolves the list of characters to make visible.
    * 
    * @return string[]
    */
    protected function resolveSelection() : array
    {
        $selected = $this->selected;
        
        if(empty($this->selected)) 
        {
            $selected = array(
                self::CHARS_WHITESPACE,
                self::CHARS_CONTROL
            );
        }
        
        $result = array();
        
        foreach($selected as $type) 
        {
            if(isset($this->characters[$type])) 
            {
                $result = array_merge($result, $this->characters[$type]);
            }
        }
        
        return $result;
    }
}
