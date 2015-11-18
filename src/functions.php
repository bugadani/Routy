<?php

namespace Routy;

function call_user_func_named(callable $callable, array $arguments)
{
    $reflection = new \ReflectionFunction($callable);

    $args = [];
    foreach ($reflection->getParameters() as $param) {
        $paramName = $param->getName();
        if (isset($arguments[ $paramName ])) {
            $args[] = $arguments[ $paramName ];
        } else {
            $args[] = $param->getDefaultValue();
        }
    }

    //ReflectionFunction has some issues with not binding $this in closures. Use call_user_func_array instead
    return call_user_func_array($callable, $args);
}