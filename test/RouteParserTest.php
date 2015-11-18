<?php

namespace Routy\Test;

use Routy\RouteParser;
use Routy\RouteParserInterface;

class RouteParserTest extends \PHPUnit_Framework_TestCase
{
    const ROUTY_PARSED_PATH = '\Routy\RouteData';
    /**
     * @var RouteParserInterface
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new RouteParser();
    }

    public function testParseStaticRoute()
    {
        $parsedRoute = $this->parser->parse('/simple/route/without/parameters');

        $this->assertInstanceOf(self::ROUTY_PARSED_PATH, $parsedRoute);
        $this->assertEquals('/simple/route/without/parameters', $parsedRoute->getPath());
        $this->assertTrue($parsedRoute->isStatic());
    }

    public function testParseDynamicRouteWithSimpleParameter()
    {
        $parsedRoute = $this->parser->parse('/simple/route/with/{parameter}');

        $this->assertInstanceOf(self::ROUTY_PARSED_PATH, $parsedRoute);
        $this->assertEquals('/simple/route/with/{parameter}', $parsedRoute->getPath());
        $this->assertArrayHasKey('parameter', $parsedRoute->getParameterPatterns());
        $this->assertFalse($parsedRoute->isStatic());
    }

    public function testParseDynamicRouteWithSpecificParameter()
    {
        $parsedRoute = $this->parser->parse('/simple/route/with/{parameter:\w+}');

        $this->assertInstanceOf(self::ROUTY_PARSED_PATH, $parsedRoute);
        $this->assertEquals('/simple/route/with/{parameter}', $parsedRoute->getPath());
        $this->assertArrayHasKey('parameter', $parsedRoute->getParameterPatterns());
        $this->assertEquals(['parameter' => '(\w+)'], $parsedRoute->getParameterPatterns());
        $this->assertFalse($parsedRoute->isStatic());
    }

    /**
     * @expectedException \Routy\Exceptions\RouteParseException
     */
    public function testParseThrowsWhenParameterAppearsTwice()
    {
        $this->parser->parse('/{parameter}/{parameter:\w+}');
    }
}