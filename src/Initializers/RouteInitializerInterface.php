<?php

namespace Routy\Initializers;

interface RouteInitializerInterface
{

    public function extras(array $data);

    public function onMatch($onMatch);

    public function name($name);
}