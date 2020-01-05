<?php
/**
 * File containing the {@see \AppUtils\XMLHelper_Converter_Decorator} class.
 * 
 * @package Application Utils
 * @subpackage XMLHelper
 * @see XMLHelper_Converter_Decorator
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Custom decorator for converting a SimpleXMLElement to
 * a meaningful JSON structure.
 *
 * @package Application Utils
 * @subpackage XMLHelper
 * @see https://hakre.wordpress.com/2013/07/10/simplexml-and-json-encode-in-php-part-iii-and-end/
 */
class XMLHelper_Converter_Decorator implements \JsonSerializable
{
   /**
    * @var \SimpleXMLElement
    */
    private $subject;
    
    const DEF_DEPTH = 512;
    
   /**
    * @var array
    */
    private $options = array(
        '@attributes' => true,
        '@text' => true,
        'depth' => self::DEF_DEPTH
    );

   /**
    * @var array|string|null
    */
    protected $result = array();
    
    public function __construct(\SimpleXMLElement $element)
    {
        $this->subject = $element;
    }
    
   /**
    * Whether to use the `@attributes` key to store element attributes.
    * 
    * @param bool $bool
    * @return XMLHelper_Converter_Decorator
    */
    public function useAttributes(bool $bool) : XMLHelper_Converter_Decorator 
    {
        $this->options['@attributes'] = (bool)$bool;
        return $this;
    }
    
   /**
    * Whether to use the `@text` key to store the node text.
    * 
    * @param bool $bool
    * @return XMLHelper_Converter_Decorator
    */
    public function useText(bool $bool) : XMLHelper_Converter_Decorator 
    {
        $this->options['@text'] = (bool)$bool;
        return $this;
    }
    
   /**
    * Set the maximum depth to parse in the document.
    * 
    * @param int $depth
    * @return XMLHelper_Converter_Decorator
    */
    public function setDepth(int $depth) : XMLHelper_Converter_Decorator 
    {
        $this->options['depth'] = (int)max(0, $depth);
        return $this;
    }
    
    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed data which can be serialized by json_encode.
     */
    public function jsonSerialize()
    {
        $this->result = array();
        
        $this->detectAttributes();
        $this->traverseChildren();
        $this->encodeTextElements();
        
        // return empty elements as NULL (self-closing or empty tags)
        if (empty($this->result) && !is_numeric($this->result) && !is_bool($this->result)) 
        {
            $this->result = NULL;
        }
        
        return $this->result;
    }
    
    protected function detectAttributes()
    {
        if(!$this->options['@attributes']) {
            return;
        }
        
        $attributes = $this->subject->attributes();
        
        if(!empty($attributes)) 
        {
            $this->result['@attributes'] = array_map('strval', iterator_to_array($attributes));
        }
    }
    
    protected function traverseChildren()
    {
        $children = $this->subject;
        $depth = $this->options['depth'] - 1;
        
        if($depth <= 0) 
        {
            $children = [];
        }
        
        // json encode child elements if any. group on duplicate names as an array.
        foreach ($children as $name => $element) 
        {
            /* @var \SimpleXMLElement $element */
            $decorator = new self($element);
            
            $decorator->options = ['depth' => $depth] + $this->options;
            
            if(isset($this->result[$name])) 
            {
                if(!is_array($this->result[$name])) 
                {
                    $this->result[$name] = [$this->result[$name]];
                }
                
                $this->result[$name][] = $decorator;
            } 
            else 
            {
                $this->result[$name] = $decorator;
            }
        }
    }
    
    protected function encodeTextElements()
    {
        // json encode non-whitespace element simplexml text values.
        $text = trim((string)$this->subject);
        
        if(strlen($text)) 
        {
            if($this->options['@text']) 
            {
                $this->result['@text'] = $text;
            } 
            else 
            {
                $this->result = $text;
            }
        }
    }
}
