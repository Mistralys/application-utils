<?php
/**
 * File containing the {@link Request_AcceptHeaders} class.
 * 
 * @package Application Utils
 * @subpackage Request
 * @see Request_AcceptHeaders
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Accept header parser: fetches the accept header string
 * and splits it into its composing mime types.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_AcceptHeaders
{
    protected $headers = array();
    
    public function __construct()
    {
        $this->parse();
    }
    
   /**
    * Retrieves an indexed array with accept mime types
    * that the client sent, in the order of preference
    * the client specified.
    *
    * Example:
    *
    * array(
    *     'text/html',
    *     'application/xhtml+xml',
    *     'image/webp'
    *     ...
    * )
    */
    public function getMimeStrings() : array
    {
        $result = array();
        
        foreach($this->headers as $header)
        {
            $result[] = $header['type'];
        }
        
        return $result;
    }
    
   /**
    * Checks that an accept header string exists, and tries to parse it.
    */
    protected function parse() : void
    {
        // we may be in a CLI environment where the headers
        // are not populated.
        if(!isset($_SERVER['HTTP_ACCEPT'])) {
            return;
        }
        
        $this->headers = $this->parseHeader($_SERVER['HTTP_ACCEPT']);
    }
    
   /**
    * Splits the accept header string and parses the mime types.
    *  
    * @param string $acceptHeader 
    */
    protected function parseHeader(string $acceptHeader) : array
    {
        $tokens = preg_split('/\s*,\s*/', $acceptHeader);
        
        $accept = array();
        
        foreach($tokens as $i => $term)
        {
            $accept[] = $this->parseEntry($i, $term);
        }
        
        usort($accept, array($this, 'sortMimeTypes'));
        
        return $accept;
    }
    
   /**
    * Parses a single mime type entry.
    * 
    * @param int $i The position in the accept string
    * @param string $mime The mime type
    * @return array
    */
    protected function parseEntry(int $i, string $mime) : array
    {
        $entry = array(
            'pos' => $i,
            'params' => array(),
            'quality' => 0,
            'type' => null
        );
        
        if(strstr($mime, ';'))
        {
            $parts = explode(';', $mime);
            $mime = array_shift($parts);
            
            // several parameters are possible, and they can be parsed
            // like an URL query string if separated by ampersands;
            $entry['params'] = ConvertHelper::parseQueryString(implode('&', $parts));
                
            if(isset($entry['params']['q'])) 
            {
                $entry['quality'] = (double)$entry['params']['q'];
            } 
        }
        
        $entry['type'] = $mime;
        
        return $entry;
    }
    
   /**
    * Sorts the mime types collection, first by quality
    * and then by position in the list.
    * 
    * @param array $a
    * @param array $b
    * @return number
    */
    protected function sortMimeTypes(array $a, array $b)
    {
        /* first tier: highest q factor wins */
        $diff = $b['quality'] - $a['quality'];
        
        if ($diff > 0) 
        {
            $diff = 1;
        } 
        else 
        {
            if ($diff < 0) 
            {
                $diff = -1;
            } 
            else 
            {
                /* tie-breaker: first listed item wins */
                $diff = $a['pos'] - $b['pos'];
            }
        }
        
        return $diff;
    }
}
