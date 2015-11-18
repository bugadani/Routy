<?php

namespace Routy;

class Match
{
    /**
     * @var Route
     */
    private $route;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Match constructor.
     *
     * @param Route $route
     * @param array $parameters
     */
    public function __construct(Route $route, array $parameters = [])
    {
        $this->route      = $route;
        $this->parameters = $parameters;
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}