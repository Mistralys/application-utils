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

use AppUtils\Request\AcceptHeader;

/**
 * Accept header parser: fetches the "Accept" header string
 * and splits it into its composing mime types.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_AcceptHeaders
{
    /**
     * @var AcceptHeader[]
     */
    protected array $headers = array();
    
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
    *
    * @return string[]
    */
    public function getMimeStrings() : array
    {
        $result = array();
        
        foreach($this->headers as $header)
        {
            $result[] = $header->getMimeType();
        }
        
        return $result;
    }
    
   /**
    * Checks that an "Accept" header string exists, and tries to parse it.
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
    * Splits the "Accept" header string and parses the mime types.
    *  
    * @param string $acceptHeader
    * @return AcceptHeader[]
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
    * @param int $i The position in the "Accept" string
    * @param string $mime The mime type
    * @return AcceptHeader
    */
    protected function parseEntry(int $i, string $mime) : AcceptHeader
    {
        $quality = 0;
        $params = array();
        
        if(strpos($mime, ';') !== false)
        {
            $parts = explode(';', $mime);
            $mime = (string)array_shift($parts);
            
            // several parameters are possible, and they can be parsed
            // like a URL query string if separated by ampersands;
            $params = ConvertHelper::parseQueryString(implode('&', $parts));
                
            if(isset($params['q']))
            {
                $quality = (double)$params['q'];
            } 
        }
        
        return new AcceptHeader(
            $mime,
            $i,
            $params,
            $quality
        );
    }

    /**
     * Sorts the mime types collection, first by quality
     * and then by position in the list.
     *
     * @param AcceptHeader $a
     * @param AcceptHeader $b
     * @return int
     */
    protected function sortMimeTypes(AcceptHeader $a, AcceptHeader $b) : int
    {
        /* first tier: highest q factor wins */
        $diff = $b->getQuality() - $a->getQuality();
        
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
                $diff = $a->getPosition() - $b->getPosition();
            }
        }
        
        return $diff;
    }
}
