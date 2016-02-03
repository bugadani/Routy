<?php

namespace Routy;

/**
 * RouteContainer contains references to the routes used by Routy.
 */
class RouteContainer implements \IteratorAggregate
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @var Route[]
     */
    private $routeNames = [];

    /**
     * @var array
     */
    private $pathMethodMap = [];

    public function add(Route $route)
    {
        $path   = $route->getParsed()->getPathPattern();
        $method = $route->getMethod();

        if (!isset($this->pathMethodMap[ $path ])) {
            $this->pathMethodMap[ $path ] = [];
        } else if (in_array($method, $this->pathMethodMap[ $path ])) {
            throw new \InvalidArgumentException("Method {$method} is already defined for {$path}");
        }

        $name = $route->getName();
        if ($name !== null) {
            if (isset($this->routeNames[ $name ])) {
                throw new \InvalidArgumentException("Route {$name} is already defined");
            }
            $this->routeNames[ $name ] = $route;
        }
        $this->pathMethodMap[ $path ][] = $method;
        $this->routes[]                 = $route;
    }

    /**
     * @param $name
     *
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
        return new \ArrayIterator($this->routes);
    }
}