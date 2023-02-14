<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser;

use AppUtils\ConvertHelper;
use AppUtils\URLInfo;
use AppUtils\URLInfo\URISchemes;
use AppUtils\URLInfo\URLHosts;
use AppUtils\URLInfo\URLInfoTrait;

class ParsedInfoFilter
{
    use URLInfoTrait;

    private string $url;

    /**
     * @param string $url
     * @param array<string,mixed> $info
     */
    public function __construct(string $url, array $info)
    {
        $this->info = $info;
        $this->url = $url;
    }

    /**
     * @return array<string,mixed>
     */
    public function filter() : array
    {
        $this->info['type'] = URLInfo::TYPE_NONE;

        $this->filterScheme();
        $this->filterAuth();
        $this->filterHost();
        $this->filterPath();
        $this->filterKnownHosts();
        $this->filterParams();
        $this->filterEmail();

        return $this->info;
    }

    /**
     * Special case for Email addresses: the path component
     * must be stripped of spaces, as no spaces are allowed.
     * This differs from the usual path behavior, which is to
     * allow spaces.
     *
     * @return void
     */
    private function filterEmail() : void
    {
        $path = $this->info['path'] ?? '';

        if(strpos($path, '@') !== false)
        {
            $this->info['path'] = str_replace(' ', '', $path);
        }
    }

    private function filterScheme() : void
    {
        if($this->hasScheme())
        {
            $this->setScheme(strtolower($this->getScheme()));
        }
        else
        {
            $scheme = URISchemes::detectScheme($this->url);
            if(!empty($scheme)) {
                $this->setScheme(URISchemes::resolveSchemeName($scheme));
            }
        }
    }

    private function filterAuth() : void
    {
        if(!$this->hasAuth()) {
            return;
        }

        $this->setAuth(
            urldecode($this->getUser()),
            urldecode($this->getPassword())
        );
    }

    private function filterHost() : void
    {
        if(!$this->hasHost())
        {
            return;
        }

        $host = strtolower($this->getHost());
        $host = str_replace(' ', '', $host);

        $this->setHost($host);
    }

    private function filterPath() : void
    {
        if($this->hasPath()) {
            $this->setPath($this->getPath());
        }
    }

    private function filterKnownHosts() : void
    {
        $host = $this->getPath();

        if(empty($host) || !URLHosts::isHostKnown($host))
        {
            return;
        }

        $this->setHost($host);
        $this->removePath();

        if(!$this->hasScheme()) {
            $this->setSchemeHTTPS();
        }
    }

    private function filterParams() : void
    {
        $this->info['params'] = array();

        $query = $this->getQuery();
        if(empty($query)) {
            return;
        }

        $this->info['params'] = ConvertHelper::parseQueryString($query);

        ksort($this->info['params']);
    }
}
