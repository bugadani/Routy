<?php

namespace Routy;

use Routy\Initializers\AggregateRouteInitializer;
use Routy\Initializers\RouteInitializer;
use Routy\Resources\PluralResourceGenerator;
use Routy\Resources\SingularResourceGenerator;

class Router
{
    /**
     * @var RouteParserInterface
     */
    private $routeParser;

    /**
     * @var RouteContainer
     */
    private $routeContainer;

    /**
     * @var RouteMatcher
     */
    private $routeMatcher;

    /**
     * @var RouteGenerator
     */
    private $routeGenerator;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $config = null)
    {
        if ($config === null) {
            $config = new Configuration();
        }
        $this->configuration = $config;

        $this->routeParser    = $config->routeParser ?: new RouteParser();
        $this->routeContainer = $config->routeContainer ?: new RouteContainer();

        $this->routeMatcher   = new RouteMatcher($this->routeContainer);
        $this->routeGenerator = new RouteGenerator($this->routeContainer, $config);
    }

    public function add($method, $path)
    {
        $routeData = $this->routeParser->parse($path);

        $methods          = explode('|', $method);
        $initializerArray = [];
        foreach ($methods as $method) {
            $initializerArray[] =  new RouteInitializer($this->routeContainer, new Route($method, $routeData));
        }

        return new AggregateRouteInitializer($initializerArray);
    }

    private function createRoute($method, $path)
    {
        $routeData = $this->routeParser->parse($path);

        return new RouteInitializer($this->routeContainer, new Route($method, $routeData));
    }

    public function get($path)
    {
        return $this->createRoute('GET', $path);
    }

    public function post($path)
    {
        return $this->createRoute('POST', $path);
    }

    public function put($path)
    {
        return $this->createRoute('PUT', $path);
    }

    public function delete($path)
    {
        return $this->createRoute('DELETE', $path);
    }

    public function head($path)
    {
        return $this->createRoute('HEAD', $path);
    }

    public function matchCurrentRequest()
    {
        return $this->match(
            Request::fromCurrentRequest($this->configuration)
        );
    }

    public function resource($name)
    {
        $resource = new SingularResourceGenerator($this, $name);
        $resource->idPattern($this->configuration->defaultParameterPattern);

        return $resource;
    }

    public function resources($singularName, $pluralName)
    {
        $resource = new PluralResourceGenerator($this, $singularName, $pluralName);
        $resource->idPattern($this->configuration->defaultParameterPattern);

        return $resource;
    }

    public function match(Request $request)
    {
        return $this->routeMatcher->match($request);
    }

    public function to($routeName, $parameters = [])
    {
        return $this->routeGenerator->generate($routeName, $parameters);
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }
}