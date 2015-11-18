<?php

namespace Routy;

class RouteData
{
    private $path;
    private $patterns;
    private $pattern;
    private $parameterNames;

    /**
     * ParserRoute constructor.
     * @param $path
     * @param array $patterns
     */
    public function __construct($path, array $patterns)
    {
        $this->path     = $path;
        $this->patterns = $patterns;

        if (!empty($patterns)) {
            $parameterNames = array_keys($patterns);

            $placeholders = array_map(
                function ($paramName) {
                    return "{{$paramName}}";
                },
                $parameterNames
            );

            $this->parameterNames = $parameterNames;
            $this->pattern        = str_replace($placeholders, $patterns, $path);
        } else {
            $this->parameterNames = [];
            $this->pattern        = $path;
        }
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getParameterPatterns()
    {
        return $this->patterns;
    }

    /**
     * @return array
     */
    public function getParameterNames()
    {
        return $this->parameterNames;
    }

    /**
     * @return string
     */
    public function getPathPattern()
    {
        return $this->pattern;
    }

    /**
     * @return boolean
     */
    public function isStatic()
    {
        return empty($this->patterns);
    }
}