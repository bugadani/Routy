<?php

namespace Routy\Initializers;

use Routy\Route;
use Routy\RouteContainer;

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

    public function __construct(RouteContainer $container, Route $route)
    {
        $this->container = $container;
        $this->route     = $route;
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
        $this->container->addNamed($name, $this->route);
        $this->route->setName($name);

        return $this;
    }
}