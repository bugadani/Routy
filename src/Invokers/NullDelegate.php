<?php

namespace Routy\Invokers;

use Routy\Match;

/**
 * Used in places where no callback is specified
 */
class NullDelegate implements DelegateInterface
{
    private static $instance;

    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    public function invoke(Match $match)
    {
    }
}