<?php

namespace Routy;

use Routy\Invokers\NamedParameterDelegate;
use Routy\Invokers\DelegateInterface;

class Route
{
    /**
     * @var RouteData
     */
    private $path;

    /**
     * @var
     */
    private $onMatch;

    /**
     * @var array
     */
    private $extras = [];

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $name;

    public function __construct($method, RouteData $path)
    {
        $this->path   = $path;
        $this->method = $method;
    }

    /**
     * @return DelegateInterface
     */
    public function getCallback()
    {
        return $this->onMatch;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return RouteData
     */
    public function getParsed()
    {
        return $this->path;
    }

    public function isMethod($method)
    {
        return $this->method === $method;
    }

    public function isStatic()
    {
        return $this->path->isStatic();
    }

    public function isPath($path)
    {
        return $this->path->getPath() === $path;
    }

    public function onMatch($onMatch)
    {
        if (!$onMatch instanceof DelegateInterface) {
            $onMatch = new NamedParameterDelegate($onMatch);
        }
        $this->onMatch = $onMatch;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $extras
     */
    public function setExtras(array $extras)
    {
        $this->extras = $extras;
    }

    /**
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }
}