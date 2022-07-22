<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\URLInfoTrait
 */

declare(strict_types=1);

namespace AppUtils\URLInfo;

use AppUtils\URLInfo;

/**
 * Trait used for classes that access a URL info array,
 * as parsed using the parse_url method.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
trait URLInfoTrait
{
    /**
     * @var array<string,mixed>
     */
    protected array $info = array();

    /**
     * @return array<string,mixed>
     */
    public function getInfo() : array
    {
        return $this->info;
    }

    public function getKey(string $name) : ?string
    {
        return $this->info[$name] ?? null;
    }

    public function getHost() : ?string
    {
        return $this->getKey('host');
    }

    public function getType() : string
    {
        return $this->getKey('type');
    }

    public function getPath() : ?string
    {
        return $this->getKey('path');
    }

    public function getScheme() : ?string
    {
        return $this->getKey('scheme');
    }

    public function setHost(string $host) : self
    {
        return $this->setKey('host', $host);
    }

    public function setHostFromEmail(string $email) : void
    {
        $parts = explode('@', $email);
        $this->setHost(array_pop($parts));
    }

    public function setPath(string $path) : self
    {
        return $this->setKey('path', $path);
    }

    public function setKey(string $name, string $value) : self
    {
        $this->info[$name] = $value;
        return $this;
    }

    public function setIP(string $ip) : self
    {
        $this->setHost($ip);
        $this->setKey('ip', $ip);

        return $this;
    }

    public function setSchemeHTTPS() : self
    {
        return $this->setScheme('https');
    }

    public function setSchemeMailto() : self
    {
        return $this->setScheme('mailto');
    }

    public function setScheme(string $scheme) : self
    {
        return $this->setKey('scheme', $scheme);
    }

    public function setTypeURL() : self
    {
        return $this->setType(URLInfo::TYPE_URL);
    }

    public function setTypeEmail() : void
    {
        $this->setType(URLInfo::TYPE_EMAIL);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type) : self
    {
        $this->info['type'] = $type;
        return $this;
    }

    public function removePath() : self
    {
        return $this->removeKey('path');
    }

    public function removeKey(string $name) : self
    {
        unset($this->info[$name]);
        return $this;
    }

    public function hasKey(string $name) : bool
    {
        return isset($this->info[$name]);
    }

    public function hasHost() : bool
    {
        return $this->hasKey('host');
    }

    public function hasQuery() : bool
    {
        return $this->hasKey('query');
    }

    public function hasScheme() : bool
    {
        return $this->hasKey('scheme');
    }

    public function hasPath() : bool
    {
        return $this->hasKey('path');
    }

    public function hasFragment() : bool
    {
        return $this->hasKey('fragment');
    }

    public function isFragmentOnly() : bool
    {
        return
            $this->hasFragment()
            &&
            (
                !$this->hasScheme()
                &&
                !$this->hasHost()
                &&
                !$this->hasPath()
                &&
                !$this->hasQuery()
            );
    }

    public function isPathOnly() : bool
    {
        return
            $this->hasPath()
            &&
            (
                !$this->hasScheme()
                &&
                !$this->hasHost()
                &&
                !$this->hasQuery()
            );
    }

    public function isHostOnly() : bool
    {
        return
            $this->hasHost()
            &&
            (
                !$this->hasScheme()
                &&
                !$this->hasPath()
                &&
                !$this->hasQuery()
            );
    }

    public function isSchemeLess() : bool
    {
        return $this->isFragmentOnly();
    }
}
