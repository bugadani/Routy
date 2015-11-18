<?php

namespace Routy;

class Configuration
{
    /**
     * Whether short-style URL generation and interpretation should be used
     *
     * @var bool
     */
    public $useShortUrls = true;

    /**
     * The key of the path, if short-style URLs are disabled
     *
     * @var string
     */
    public $pathKey = 'q';

    /**
     * Whether emulating certain HTTP methods should be enabled
     *
     * @var bool
     */
    public $emulateMethodsWhenPost = true;

    /**
     * If emulation is enabled, this POST key will be searched for the emulated method
     *
     * @var string
     */
    public $emulateMethodsKey = '_method';

    /**
     * Methods that can be emulated
     *
     * @var array
     */
    public $emulatedMethods = ['PUT', 'DELETE'];

    /**
     * The parser used to parse route definitions
     *
     * @var RouteParser Supply null to use the default
     */
    public $routeParser;

    /**
     * The route container used to store routing information
     *
     * @var RouteContainer Supply null to use the default
     */
    public $routeContainer;

    /**
     * The default format used to match parameters
     *
     * @var string
     */
    public $defaultParameterPattern = '[^/]+';
}