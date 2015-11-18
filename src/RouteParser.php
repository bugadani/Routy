<?php

namespace Routy;

class RouteParser implements RouteParserInterface
{
    private $defaultPattern;

    public function __construct(Configuration $configuration = null)
    {
        if ($configuration === null) {
            $configuration = new Configuration();
        }

        $this->defaultPattern = $configuration->defaultParameterPattern;
    }

    /**
     * @inheritdoc
     */
    public function parse($path)
    {
        $patterns = [];
        try {
            $path = preg_replace_callback(
                '/{(\w+)(?::(.*?))?}/',
                function ($matches) use (&$patterns) {
                    if (!isset($matches[2])) {
                        $matches[2] = $this->defaultPattern;
                    }
                    list(, $placeholder, $pattern) = $matches;
                    if (isset($patterns[ $placeholder ])) {
                        throw new \InvalidArgumentException(
                            "Parameter {$placeholder} is defined multiple times in path"
                        );
                    }
                    $patterns[ $placeholder ] = "({$pattern})";

                    return "{{$placeholder}}";
                },
                $path
            );
        } catch (\InvalidArgumentException $e) {
            throw new Exceptions\RouteParseException("Route '{$path}' could not be parsed.", 0, $e);
        }

        return new RouteData($path, $patterns);
    }
}