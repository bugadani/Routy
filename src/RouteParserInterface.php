<?php

namespace Routy;

interface RouteParserInterface
{

    /**
     * @param $path
     * @return RouteData
     */
    public function parse($path);
}