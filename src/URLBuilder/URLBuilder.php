<?php
/**
 * @package Application Utils
 * @subpackage URL Builder
 */

declare(strict_types=1);

namespace AppUtils\URLBuilder;

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\FileHelper;
use AppUtils\Request;
use AppUtils\Traits\RenderableTrait;
use AppUtils\URLInfo;
use function AppUtils\parseURL;

/**
 * Helper class used to build application URLs guaranteeing
 * that all generated URLs use the same base URL, as configured
 * in the request object (see {@see Request::setBaseURL()}).
 *
 * ## Usage
 *
 * To create an instance, use the {@see self::create()} method.
 *
 * ## Extending the URL builder
 *
 * The URLBuilder class is designed to be extended, so you can
 * add methods to handle parameters specific to your application.
 * Typically, you would create a new class that extends {@see URLBuilder},
 * as well as a matching interface that extends {@see URLBuilderInterface}.
 *
 * @package Application Utils
 * @subpackage URL Builder
 * @see URLBuilderInterface
 */
class URLBuilder implements URLBuilderInterface
{
    use RenderableTrait;

    /**
     * @var array<string,string>
     */
    private array $params;

    private string $dispatcher = '';

    /**
     * @param array<string,string|int|float|bool|null> $params
     */
    public function __construct(array $params=array())
    {
        $this->import($params);
    }

    public function getDispatcher() : string
    {
        return $this->dispatcher;
    }

    public static function create(array $params=array()) : self
    {
        return new self($params);
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function remove(string $name) : self
    {
        if(isset($this->params[$name])) {
            unset($this->params[$name]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function inheritParam(string $name): self
    {
        $value = Request::getInstance()->getParam($name);

        if($value !== null && $value !== '') {
            return $this->auto($name, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function import(array $params) : self
    {
        foreach($params as $param => $value) {
            $this->auto($param, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws URLBuilderException {@see URLBuilderException::ERROR_INVALID_HOST}
     */
    public function importURL(string $url) : self
    {
        $parsed = parseURL($url);

        $this->checkHost($parsed);

        return $this
            ->dispatcher(ltrim($parsed->getPath(), '/'))
            ->import($parsed->getParams());
    }

    private ?URLInfo $appURL = null;

    private function getAppURL() : URLInfo
    {
        if(!isset($this->appURL)) {
            $this->appURL = parseURL(Request::getInstance()->getBaseURL());
        }

        return $this->appURL;
    }

    /**
     * Ensures that the specified URL host matches the current application host.
     * @param URLInfo $url
     * @return void
     * @throws URLBuilderException {@see URLBuilderException::ERROR_INVALID_HOST}
     */
    private function checkHost(URLInfo $url) : void
    {
        $appURL = $this->getAppURL();

        $host = str_replace('www.', '', $url->getHost());
        $expected = str_replace('www.', '', $appURL->getHost());

        if($host === $expected) {
            return;
        }

        throw new URLBuilderException(
            'Invalid host in URL.',
            sprintf(
                'Cannot import URL: The host [%s] in the URL does not match the current application host [%s]. '.PHP_EOL.
                'Target URL was: '.PHP_EOL.
                '%s',
                $url->getHost(),
                $appURL->getHost(),
                $url
            ),
            URLBuilderException::ERROR_INVALID_HOST
        );
    }

    /**
     * Adds a parameter, automatically determining its type.
     *
     * @param string $name
     * @param string|int|float|bool|null $value
     * @return $this
     */
    public function auto(string $name, $value) : self
    {
        if(is_bool($value)) {
            return $this->bool($name, $value);
        }

        if(is_string($value) || is_int($value) || is_float($value)) {
            return $this->string($name, (string)$value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param int $value
     * @return $this
     */
    public function int(string $name, int $value) : self
    {
        return $this->string($name, (string)$value);
    }

    /**
     * @param string $name
     * @param float $value
     * @return $this
     */
    public function float(string $name, float $value) : self
    {
        return $this->string($name, (string)$value);
    }

    /**
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function string(string $name, ?string $value) : self
    {
        if(!empty($value)) {
            $this->params[$name] = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param bool $value
     * @param bool $yesNo
     * @return $this
     */
    public function bool(string $name, bool $value, bool $yesNo=false) : self
    {
        return $this->string($name, ConvertHelper::bool2string($value, $yesNo));
    }

    /**
     * Adds an array as a JSON string URL parameter.
     * @param string $name
     * @param array<int|string,string|int|float|bool|NULL|array> $data
     * @return $this
     * @throws JSONConverterException
     */
    public function arrayJSON(string $name, array $data) : self
    {
        return $this->string($name, JSONConverter::var2json($data));
    }

    /**
     * Sets the name of the dispatcher script to use in the URL.
     * @param string $dispatcher
     * @return $this
     */
    public function dispatcher(string $dispatcher) : self
    {
        // When importing an application URL, the base URL may already
        // contain a path, so we need to remove it.
        $basePath = trim($this->getAppURL()->getPath(), '/');
        $dispatcher = trim(str_replace($basePath, '', trim($dispatcher, '/')), '/');

        // Enforce that non-file dispatcher paths end with a slash
        if(!empty($dispatcher) && FileHelper::getExtension($dispatcher) === '') {
            $dispatcher .= '/';
        }

        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @return string The generated URL with all parameters.
     */
    public function get() : string
    {
        return Request::getInstance()->buildURL($this->params, $this->dispatcher);
    }

    public function render(): string
    {
        return $this->get();
    }

    /**
     * @return array<string,string>
     */
    public function getParams() : array
    {
        ksort($this->params);

        return $this->params;
    }

    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }
}
