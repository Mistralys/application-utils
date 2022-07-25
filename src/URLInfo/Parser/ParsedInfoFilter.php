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

    public function __construct(string $url, array $info)
    {
        $this->info = $info;
        $this->url = $url;
    }

    public function filter() : array
    {
        $this->info['type'] = URLInfo::TYPE_NONE;

        $this->filterScheme();
        $this->filterAuth();
        $this->filterHost();
        $this->filterPath();
        $this->filterKnownHosts();
        $this->filterParams();

        return $this->info;
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
            urldecode((string)$this->getUser()),
            urldecode((string)$this->getPassword())
        );
    }

    private function filterHost() : void
    {
        if(!$this->hasHost())
        {
            return;
        }

        $host = strtolower((string)$this->getHost());
        $host = str_replace(' ', '', $host);

        $this->setHost($host);
    }

    private function filterPath() : void
    {
        if($this->hasPath()) {
            $this->setPath(str_replace(' ', '', $this->getPath()));
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
