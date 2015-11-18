<?php

namespace Routy\Invokers;

use Routy\Match;

/**
 * A simple wrapper that calls the callback with named parameters
 */
class NamedParameterDelegate implements DelegateInterface
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function invoke(Match $match)
    {
        $extras = $match->getRoute()->getExtras();
        \Routy\call_user_func_named($this->callback, $match->getParameters() + $extras);
    }
}