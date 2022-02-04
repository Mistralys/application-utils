<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

class FileDownloader
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $timeout = 14;

    /**
     * @var bool
     */
    private $SSLEnabled = true;

    private function __construct(string $url)
    {
        $this->url = $url;
    }

    public static function factory(string $url) : FileDownloader
    {
        return new FileDownloader($url);
    }

    /**
     * @param int $timeout
     * @return FileDownloader
     */
    public function setTimeout(int $timeout) : FileDownloader
    {
        if($timeout > 0)
        {
            $this->timeout = $timeout;
        }

        return $this;
    }

    /**
     * @param bool $enabled
     * @return FileDownloader
     */
    public function setSSLEnabled(bool $enabled=true) : FileDownloader
    {
        $this->SSLEnabled = $enabled;
        return $this;
    }

    /**
     * Uses cURL to download the contents of the specified URL,
     * returns the content.
     *
     * @throws FileHelper_Exception
     * @return string
     *
     * @see FileHelper::ERROR_CANNOT_OPEN_URL
     */
    public function download() : string
    {
        $ch = $this->initCurl();

        $output = curl_exec($ch);

        if($output === false)
        {
            throw new FileHelper_Exception(
                'Unable to open URL',
                sprintf(
                    'Tried accessing URL "%1$s" using cURL, but the request failed. cURL error: %2$s',
                    $this->url,
                    curl_error($ch)
                ),
                FileHelper::ERROR_CANNOT_OPEN_URL
            );
        }

        curl_close($ch);

        if(is_string($output))
        {
            return $output;
        }

        throw new FileHelper_Exception(
            'Unexpected cURL output.',
            'The cURL output is not a string, although the CURLOPT_RETURNTRANSFER option is set.',
            FileHelper::ERROR_CURL_OUTPUT_NOT_STRING
        );
    }

    /**
     * @return resource
     * @throws FileHelper_Exception
     */
    private function initCurl()
    {
        $ch = curl_init();

        if(!is_resource($ch))
        {
            throw new FileHelper_Exception(
                'Could not initialize a new cURL instance.',
                'Calling curl_init returned false. Additional information is not available.',
                FileHelper::ERROR_CURL_INIT_FAILED
            );
        }

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_REFERER, $this->url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Google Chrome/1.0");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        if(!$this->SSLEnabled)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        return $ch;
    }
}
