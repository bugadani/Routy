<?php

namespace Routy;

use Routy\Initializers\RouteInitializer;

/**
 * RouteContainer contains references to the routes used by Routy.
 */
class RouteContainer implements \IteratorAggregate
{
    /**
     * @var \SplObjectStorage
     */
    private $routes;

    /**
     * @var Route[]
     */
    private $routeNames = [];

    /**
     * @var array
     */
    private $pathMethodMap = [];

    public function __construct()
    {
        $this->routes = new \SplObjectStorage();
    }

    public function add(Route $route)
    {
        $path   = $route->getParsed()->getPathPattern();
        $method = $route->getMethod();

        if (!isset($this->pathMethodMap[ $path ])) {
            $this->pathMethodMap[ $path ] = [$method];
        } else {
            if (in_array($method, $this->pathMethodMap[ $path ])) {
                throw new \InvalidArgumentException("Method {$method} is already defined for {$path}");
            }
            $this->pathMethodMap[ $path ][] = $method;
        }
        $this->routes->attach($route);

        return new RouteInitializer($this, $route);
    }

    public function addNamed($name, Route $route)
    {
        if (!$this->routes->contains($route)) {
            $this->add($route);
        }

        if (isset($this->routeNames[ $name ])) {
            throw new \InvalidArgumentException("Route {$name} is already defined");
        }
        $this->routeNames[ $name ] = $route;
    }

    /**
     * @param $name
     * @return Route
     */
    public function get($name)
    {
        return $this->routeNames[ $name ];
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->routes;
    }
}