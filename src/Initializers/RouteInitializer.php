<?php

namespace Routy\Initializers;

use Routy\Route;
use Routy\RouteContainer;
use Routy\RouteData;

class RouteInitializer implements RouteInitializerInterface
{
    /**
     * @var RouteContainer
     */
    private $container;

    /**
     * @var Route
     */
    private $route;

    public function __construct(RouteContainer $container, $method, RouteData $routeData)
    {
        $this->container = $container;
        $this->route     = new Route($method, $routeData);
    }

    public function extras(array $data)
    {
        $this->route->setExtras($data);

        return $this;
    }

    public function onMatch($onMatch)
    {
        $this->route->onMatch($onMatch);

        return $this;
    }

    public function name($name)
    {
        $this->route->setName($name);

        return $this;
    }

    public function __destruct()
    {
        $this->container->add($this->route);
    }
}