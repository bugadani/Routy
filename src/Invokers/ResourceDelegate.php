<?php

namespace Routy\Invokers;

use Routy\Match;

/**
 * Used to invoke match callbacks with resource-style parameters
 */
class ResourceDelegate implements DelegateInterface
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function invoke(Match $match)
    {
        $route  = $match->getRoute();
        $extras = $route->getExtras();
        \Routy\call_user_func_named(
            $this->callback,
            [
                'controller' => $extras['controller'],
                'action'     => $extras['action'],
                'parameters' => $match->getParameters()
            ]
        );
    }
}