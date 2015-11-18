<?php

namespace Routy\Initializers;

class AggregateRouteInitializer implements RouteInitializerInterface
{
    /**
     * @var RouteInitializerInterface[]
     */
    private $initializerArray;

    public function __construct(array $initializerArray)
    {
        $this->initializerArray = $initializerArray;
    }

    public function extras(array $data)
    {
        foreach ($this->initializerArray as $initializer) {
            $initializer->extras($data);
        }

        return $this;
    }

    public function onMatch($onMatch)
    {
        foreach ($this->initializerArray as $initializer) {
            $initializer->onMatch($onMatch);
        }

        return $this;
    }

    public function name($name)
    {
        foreach ($this->initializerArray as $initializer) {
            $initializer->name($name);
        }

        return $this;
    }
}