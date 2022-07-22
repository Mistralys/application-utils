<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\URIConnectionTester
 */

declare(strict_types=1);

namespace AppUtils\URLInfo;

use AppUtils\BaseException;
use AppUtils\Interface_Optionable;
use AppUtils\Traits_Optionable;
use AppUtils\URLInfo;

/**
 * Used to test whether a URL exists / can be connected to.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URIConnectionTester implements Interface_Optionable
{
    use Traits_Optionable;
    
    private URLInfo $url;
    
    public function __construct(URLInfo $url)
    {
        $this->url = $url;
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'verify-ssl' => true,
            'curl-verbose' => false,
            'timeout' => 10
        );
    }
    
   /**
    * Whether to verify the host's SSL certificate, in
    * case of a https connection.
    * 
    * @param bool $verifySSL
    * @return URIConnectionTester
    */
    public function setVerifySSL(bool $verifySSL=true) : URIConnectionTester
    {
        $this->setOption('verify-ssl', $verifySSL);
            return $this;
    }
    
    public function isVerifySSLEnabled() : bool
    {
        return $this->getBoolOption('verify-ssl');
    }
    
    public function setVerboseMode(bool $enabled=true) : URIConnectionTester
    {
        $this->setOption('curl-verbose', $enabled);
        return $this;
    }
    
    public function isVerboseModeEnabled() : bool
    {
        return $this->getBoolOption('curl-verbose');
    }
    
    public function setTimeout(int $seconds) : URIConnectionTester#
    {
        $this->setOption('timeout', $seconds);
        return $this;
    }
    
    public function getTimeout() : int
    {
        return $this->getIntOption('timeout');
    }
    
   /**
    * Initializes the CURL instance.
    * 
    * @throws BaseException
    * @return resource
    */
    private function initCURL()
    {
        $ch = curl_init();
        
        if(!is_resource($ch))
        {
            throw new URLException(
                'Could not initialize a new cURL instance.',
                'Calling curl_init returned false. Additional information is not available.',
                URLInfo::ERROR_CURL_INIT_FAILED
            );
        }
        
        return $ch;
    }
    
   /**
    * @param resource $ch
    */
    private function configureOptions($ch) : void
    {
        if($this->isVerboseModeEnabled())
        {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        
        curl_setopt($ch, CURLOPT_URL, $this->url->getNormalized());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if(!$this->isVerifySSLEnabled())
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        
        if($this->url->hasUsername())
        {
            curl_setopt($ch, CURLOPT_USERNAME, $this->url->getUsername());
            curl_setopt($ch, CURLOPT_PASSWORD, $this->url->getPassword());
        }
    }
        
    public function canConnect() : bool
    {
        $ch = $this->initCURL();
        
        $this->configureOptions($ch);
        
        curl_exec($ch);
        
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return ($http_code === 200) || ($http_code === 302);
    }
}
