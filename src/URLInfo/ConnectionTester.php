<?php
/**
 * File containing the {@see AppUtils\URLInfo_ConnectionTester} class.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @see AppUtils\URLInfo_ConnectionTester
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Used to test whether an URL exists / can be connected to.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLInfo_ConnectionTester implements Interface_Optionable
{
    use Traits_Optionable;
    
   /**
    * @var URLInfo
    */
    private $url;
    
    public function __construct(URLInfo $url)
    {
        $this->url = $url;
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'verify-ssl' => true,
            'curl-verbose' => false
        );
    }
    
   /**
    * Whether to verify the host's SSL certificate, in
    * case of an https connection.
    * 
    * @param bool $verifySSL
    * @return URLInfo_ConnectionTester
    */
    public function setVerifySSL(bool $verifySSL=true) : URLInfo_ConnectionTester
    {
        $this->setOption('verify-ssl', $verifySSL);
            return $this;
    }
    
    public function isVerifySSLEnabled() : bool
    {
        return $this->getBoolOption('verify-ssl');
    }
    
    public function setVerboseMode(bool $enabled=true) : URLInfo_ConnectionTester
    {
        $this->setOption('curl-verbose', $enabled);
        return $this;
    }
    
    public function isVerboseModeEnabled() : bool
    {
        return $this->getBoolOption('curl-verbose');
    }
    
    public function canConnect() : bool
    {
        requireCURL();
        
        $ch = curl_init();
        
        if(!is_resource($ch))
        {
            throw new BaseException(
                'Could not initialize a new cURL instance.',
                'Calling curl_init returned false. Additional information is not available.',
                self::ERROR_CURL_INIT_FAILED
            );
        }
        
        if($this->isVerboseModeEnabled())
        {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        
        curl_setopt($ch, CURLOPT_URL, $this->url->getNormalized());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
        
        curl_exec($ch);
        
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return ($http_code === 200) || ($http_code === 302);
    }
}
