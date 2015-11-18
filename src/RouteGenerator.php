<?php

namespace Routy;

class RouteGenerator
{
    /**
     * @var RouteContainer
     */
    private $container;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(RouteContainer $container, Configuration $configuration)
    {
        $this->container     = $container;
        $this->configuration = $configuration ?: new Configuration();
    }

    public function generate($routeName, array $parameters = [])
    {
        $route = $this->container->get($routeName);

        $routeData = $route->getParsed();

        return $this->buildPath($routeData->getPath(), $parameters, $routeData->getParameterNames());
    }

    private function buildPath($path, array $parameters, array $parameterNames)
    {
        //Collect values of placeholders
        $replace = [];
        $missing = [];
        foreach ($parameterNames as $name) {
            if (!isset($parameters[ $name ])) {
                $missing[] = $name;
            } else {
                $replace[ '{' . $name . '}' ] = $parameters[ $name ];

                //Delete the item because the remaining parameters will be appended to the url
                unset($parameters[ $name ]);
            }
        }

        //Check if there are any missing parameters
        if (!empty($missing)) {
            $params = join(', ', $missing);
            throw new \InvalidArgumentException("Parameters not set: {$params}");
        }

        //Prepend prefix and replace placeholders
        $path = strtr($path, $replace);

        //If short urls are disabled, create a long url format with pathKey as key
        if (!$this->configuration->useShortUrls) {
            $path = "?{$this->configuration->pathKey}={$path}";
        }

        //Append the rest of the parameters
        if (!empty($parameters)) {
            $path .= (strpos($path, '?') === false) ? '?' : '&';
        }

        //Build the final url
        return $path . http_build_query($parameters, null, '&');
    }
}