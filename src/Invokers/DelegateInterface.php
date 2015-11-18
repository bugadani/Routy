<?php

namespace Routy\Invokers;

use Routy\Match;

/**
 * Implementations of DelegateInterface are used to decorate route match callback functions
 */
interface DelegateInterface
{
    public function invoke(Match $match);
}