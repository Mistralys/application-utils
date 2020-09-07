<?php
/**
 * File containing the {@link StringBuilder} class.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @see StringBuilder
 */

declare(strict_types=1);

namespace AppUtils;

use DateTime;
use AppLocalize;

/**
 * Utility class used to easily concatenate strings
 * with a chainable interface. 
 * 
 * Each bit of text that is added is automatically 
 * separated by spaces, making it easy to write
 * texts without handling this separately.
 * 
 * Specialized methods help in quickly formatting 
 * text, or adding common HTML-based contents.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see StringBuilder
 */
class StringBuilder implements StringBuilder_Interface
{
   /**
    * @var string
    */
    protected $separator = ' ';

   /**
    * @var string[]
    */
    protected $strings = array();

   /**
    * @var string
    */
    protected $mode = 'html';

   /**
    * @var string
    */
    protected $noSpace = '§!§';
    
    public function __construct()
    {
        
    }
    
   /**
    * Adds a subject as a string. Is ignored if empty.
    * 
    * @param string|number|StringBuilder_Interface $string
    * @return $this
    */
    public function add($string) : StringBuilder
    {
        $string = strval($string);
        
        if(!empty($string)) 
        {
            $this->strings[] = $string;
        }
        
        return $this;
    }
    
   /**
    * Adds a string without appending an automatic space.
    * 
    * @param string|number|StringBuilder_Interface $string
    * @return $this
    */
    public function nospace($string) : StringBuilder
    {
        return $this->add($this->noSpace.strval($string));
    }
    
   /**
    * Adds raw HTML code. Does not add an automatic space.
    * 
    * @param string|number|StringBuilder_Interface $html
    * @return $this
    */
    public function html($html) : StringBuilder
    {
        return $this->nospace($html);
    }
    
   /**
    * Adds an unordered list with the specified items.
    * 
    * @param array<int,string|number|StringBuilder_Interface> $items
    * @return $this
    */
    public function ul(array $items) : StringBuilder
    {
        return $this->list('ul', $items);
    }
    
   /**
    * Adds an ordered list with the specified items.
    * 
    * @param array<int,string|number|StringBuilder_Interface> $items
    * @return $this
    */
    public function ol(array $items) : StringBuilder
    {
        return $this->list('ol', $items);
    }
    
   /**
    * Creates a list tag with the items list.
    * 
    * @param string $type The list type, `ol` or `ul`.
    * @param array<int,string|number|StringBuilder_Interface> $items
    * @return $this
    */
    protected function list(string $type, array $items) : StringBuilder
    {
        return $this->html(sprintf(
            '<%1$s><li>%2$s</li></%1$s>',
            $type,
            implode('</li><li>', $items)
        ));
    }
    
   /**
    * Add a translated string.
    * 
    * @param string $format The native string to translate.
    * @param array<int,mixed> $arguments The variables to inject into the translated string, if any.
    * @return $this
    */
    public function t(string $format, ...$arguments) : StringBuilder
    {
        array_unshift($arguments, $format);
        
        if(!class_exists('\AppLocalize\Localization'))
        {
            return $this->sf(...$arguments);
        }
        
        return $this->add(call_user_func_array(
            array(AppLocalize\Localization::getTranslator(), 'translate'),
            $arguments
        ));
    }
    
   /**
    * Adds a "5 months ago" age since the specified date.
    * 
    * @param DateTime $since
    * @return $this
    */
    public function age(DateTime $since) : StringBuilder
    {
        return $this->add(ConvertHelper::duration2string($since));
    }
    
   /**
    * Adds HTML quotes around the string.
    * 
    * @param string|number|StringBuilder_Interface $string
    * @return $this
    */
    public function quote($string)
    {
        return $this->sf('&quot;%s&quot;', strval($string));
    }
    
   /**
    * Adds a text that is meant as a reference to an UI element,
    * like a menu item, button, etc.
    * 
    * @param string|number|StringBuilder_Interface $string 
    * @return $this
    */
    public function reference($string) : StringBuilder
    {
        return $this->sf('"%s"', $string);
    }

   /**
    * Add a string using the `sprintf` method.
    * 
    * @param string $format The format string
    * @param string|number|StringBuilder_Interface ...$arguments The variables to inject
    * @return $this
    */
    public function sf(string $format, ...$arguments) : StringBuilder
    {
        array_unshift($arguments, $format);
        
        return $this->add(call_user_func_array('sprintf', $arguments));
    }
    
   /**
    * Adds a bold string.
    * 
    * @param string|number|StringBuilder_Interface $string
    * @return $this
    */
    public function bold($string) : StringBuilder
    {
        return $this->sf(
            '<b>%s</b>', 
            strval($string)
        );
    }
    
   /**
    * Adds a HTML `br` tag.
    * 
    * @return $this
    */
    public function nl() : StringBuilder
    {
        return $this->html('<br>');
    }
    
   /**
    * Adds the current time, in the format <code>H:i:s</code>.
    * 
    * @return $this
    */
    public function time() : StringBuilder
    {
        return $this->add(date('H:i:s'));
    }
    
   /**
    * Adds the "Note:" text.
    * 
    * @return $this
    */
    public function note() : StringBuilder
    {
        return $this->t('Note:');
    }
    
   /**
    * Like `note()`, but as bold text.
    * 
    * @return $this
    */
    public function noteBold() : StringBuilder
    {
        return $this->bold(sb()->note());
    }
    
   /**
    * Adds the "Hint:" text.
    * 
    * @return $this
    */
    public function hint() : StringBuilder
    {
        return $this->t('Hint:');
    }
    
   /**
    * Adds two linebreaks.
    * 
    * @return $this
    */
    public function para() : StringBuilder
    {
        return $this->nl()->nl();
    }
    
   /**
    * Adds an anchor HTML tag.
    * 
    * @param string $label
    * @param string $url
    * @param bool $newTab
    * @return $this
    */
    public function link(string $label, string $url, bool $newTab=false) : StringBuilder
    {
        $target = '';
        if($newTab) {
            $target = ' target="_blank"';
        }
       
        return $this->sf(
            '<a href="%s"%s>%s</a>',
            $url,
            $target,
            $label
        );
    }
    
   /**
    * Wraps the string in a `code` tag.
    * 
    * @param string|number|StringBuilder_Interface $string
    * @return $this
    */
    public function code($string) : StringBuilder
    {
        return $this->sf(
            '<code>%s</code>',
            strval($string)
        );
    }
    
   /**
    * Wraps the string in a `pre` tag.
    * 
    * @param string|number|StringBuilder_Interface $string
    * @return $this
    */
    public function pre($string) : StringBuilder
    {
        return $this->sf('<pre>%s</pre>', strval($string));
    }
    
   /**
    * Wraps the text in a `span` tag with the specified classes.
    * 
    * @param string|number|StringBuilder_Interface $string
    * @param string|string[] $classes
    * @return $this
    */
    protected function spanned($string, $classes) : StringBuilder
    {
        if(!is_array($classes)) 
        {
            $classes = array(strval($classes));
        }
        
        return $this->sf(
            '<span class="%s">%s</span>',
            implode(' ', $classes),
            strval($string)
        );
    }
    
    public function render() : string
    {
        $result = implode($this->separator, $this->strings);
        
        return str_replace(array(' '.$this->noSpace, $this->noSpace), '', $result);
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function display() : void
    {
        echo $this->render();
    }
}
