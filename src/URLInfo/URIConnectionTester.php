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
use AppUtils\RequestHelper;
use AppUtils\Traits_Optionable;
use AppUtils\URLInfo;
use CurlHandle;

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
    * @param resource|CurlHandle $ch
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
        $ch = RequestHelper::createCURL();
        
        $this->configureOptions($ch);
        
        curl_exec($ch);
        
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return ($http_code === 200) || ($http_code === 302);
    }
}
