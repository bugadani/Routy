<?php

namespace Routy;

use Routy\Exceptions\MethodNotAllowed;
use Routy\Exceptions\NotFound;
use Routy\Invokers\DelegateInterface;

class RouteMatcher
{
    const CHUNK_SIZE = 10;

    /**
     * @var RouteContainer
     */
    private $routeContainer;
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(RouteContainer $routeContainer, Configuration $configuration)
    {
        $this->routeContainer = $routeContainer;
        $this->configuration  = $configuration;
    }

    public function match(Request $request)
    {
        //match static routes
        $otherMethodMatched = false;
        $dynamicRoutes      = [];
        /** @var Route $route */
        foreach ($this->routeContainer as $route) {
            if ($route->isStatic()) {
                if ($route->isPath($request->getPath())) {
                    if ($route->isMethod($request->getMethod())) {
                        return $this->createMatch($route, $request->getExtras());
                    } else {
                        $otherMethodMatched = true;
                    }
                }
            } else {
                $dynamicRoutes[] = $route;
            }
        }

        //group before matching so always CHUNK_SIZE number of different paths and not routes are matched at once
        $groupedDynamicRoutes = $this->groupRoutesByPattern($dynamicRoutes);

        //match dynamic routes
        $chunks = array_chunk($groupedDynamicRoutes, self::CHUNK_SIZE, true);
        foreach ($chunks as $routes) {
            $match = $this->matchVariableRouteChunk($request->getPath(), $routes);
            if ($match) {
                list($matchedRoutes, $params) = $match;
                foreach ($matchedRoutes as $route) {
                    if ($route->isMethod($request->getMethod())) {
                        //Map parameters to their names
                        $parameters = $this->mapOrderedParametersToNames(
                            $route->getParsed()->getParameterNames(),
                            $params
                        );

                        return $this->createMatch($route, $parameters + $request->getExtras());
                    } else {
                        $otherMethodMatched = true;
                    }
                }
            }
        }

        if ($otherMethodMatched) {
            throw new MethodNotAllowed("{$request->getMethod()} is not allowed for {$request->getPath()}");
        } else {
            throw new NotFound("Route not found: {$request->getPath()}");
        }
    }

    private function createMatch(Route $route, array $params)
    {
        $match    = new Match($route, $params);
        $callback = $route->getCallback();
        if ($callback === null) {
            $callback = $this->configuration->defaultMatchCallback;
        }
        if ($callback instanceof DelegateInterface) {
            $callback->invoke($match);
        }

        return $match;
    }

    private function matchVariableRouteChunk($path, $routesMap)
    {
        $pattern          = '';
        $numGroups        = 0;
        $matchedGroupsMap = [];

        //Create the regular expression pattern and create a [group number => route array] mapping
        foreach ($routesMap as $routePattern => $array) {
            $numVariables = $array['nVariables'];

            $pattern .= '|' . $routePattern;
            if ($numVariables < $numGroups && !isset($matchedGroupsMap[ $numVariables ])) {
                $matchedGroupsMap[ $numVariables ] = $array['routes'];
            } else {
                $numGroups = max($numGroups, $numVariables);
                $pattern .= str_repeat('()', $numGroups - $numVariables);
                $matchedGroupsMap[ $numGroups++ ] = $array['routes'];
            }
        }

        if (!preg_match('#^(?' . $pattern . ')$#', $path, $matched)) {
            return false;
        }

        //Remove the whole match
        array_shift($matched);

        //Return the route(s) based on the matched group count and the matched groups
        return [$matchedGroupsMap[ count($matched) ], $matched];
    }

    /**
     * Combine an array of values to an array of names
     *
     * @param $parameterNames
     * @param $params
     *
     * @return array
     */
    private function mapOrderedParametersToNames($parameterNames, $params)
    {
        //get the elements of $matched that are actually parameters
        $parameters = array_intersect_key($params, $parameterNames);

        return array_combine($parameterNames, $parameters);
    }

    /**
     * @param Route[] $variableRoutes
     *
     * @return array
     */
    private function groupRoutesByPattern($variableRoutes)
    {
        //Group routes by pattern
        $routesMap = [];
        foreach ($variableRoutes as $route) {

            $routeData   = $route->getParsed();
            $pathPattern = $routeData->getPathPattern();

            if (!isset($routesMap[ $pathPattern ])) {
                $patterns = $routeData->getParameterPatterns();

                $routesMap[ $pathPattern ] = [
                    'routes'     => [],
                    'nVariables' => count($patterns)
                ];
            }

            $routesMap[ $pathPattern ]['routes'][] = $route;
        }

        return $routesMap;
    }
}