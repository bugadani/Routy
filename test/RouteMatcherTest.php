<?php

namespace Routy\Test;

use Routy\Configuration;
use Routy\Request;
use Routy\Route;
use Routy\RouteContainer;
use Routy\RouteData;
use Routy\RouteMatcher;

class RouteMatcherTest extends \PHPUnit_Framework_TestCase
{

    public function testSimpleRoutesAreMatched()
    {
        $routeA = new Route(
            'GET',
            new RouteData('path', [])
        );
        $routeB = new Route(
            'POST',
            new RouteData('path', [])
        );

        $routes = new RouteContainer();
        $routes->add($routeA);
        $routes->add($routeB);

        $matcher = new RouteMatcher($routes, new Configuration());

        $match = $matcher->match(new Request(Request::METHOD_GET, 'path'));

        $this->assertSame($routeA, $match->getRoute());
    }

    /**
     * @expectedException \Routy\Exceptions\MethodNotAllowed
     */
    public function testSimpleRoute405()
    {
        $routeA = new Route(
            'GET',
            new RouteData('path', [])
        );

        $routes = new RouteContainer();
        $routes->add($routeA);
        $matcher = new RouteMatcher($routes, new Configuration());
        $matcher->match(new Request(Request::METHOD_POST, 'path'));
    }

    /**
     * @expectedException \Routy\Exceptions\NotFound
     */
    public function testSimpleRoute404()
    {
        $routeA = new Route(
            'GET',
            new RouteData('path', [])
        );

        $routes = new RouteContainer();
        $routes->add($routeA);
        $matcher = new RouteMatcher($routes, new Configuration());

        $matcher->match(new Request(Request::METHOD_POST, 'something'));
    }

    public function testDynamicRoute()
    {
        $routeA = new Route(
            'GET',
            new RouteData('path/{id}', ['id' => '([^/]+)'])
        );

        $routes = new RouteContainer();
        $routes->add($routeA);
        $matcher = new RouteMatcher($routes, new Configuration());

        $match = $matcher->match(new Request(Request::METHOD_GET, 'path/5'));

        $this->assertSame($routeA, $match->getRoute());
    }

    public function testDynamicRouteWithPattern()
    {
        $routeA = new Route(
            'GET',
            new RouteData('path/{id}', ['id' => '([0]+)'])
        );
        $routeB = new Route(
            'GET',
            new RouteData('path/{id}', ['id' => '([1]+)'])
        );

        $routes = new RouteContainer();
        $routes->add($routeA);
        $routes->add($routeB);

        $matcher = new RouteMatcher($routes, new Configuration());

        $match = $matcher->match(new Request(Request::METHOD_GET, 'path/1'));

        $this->assertSame($routeB, $match->getRoute());
    }

    /**
     * @expectedException \Routy\Exceptions\MethodNotAllowed
     */
    public function testDynamicRouteWithWrongMethod()
    {
        $routeA = new Route(
            'GET',
            new RouteData('path/{id}', ['id' => '([^/]+)'])
        );

        $routes = new RouteContainer();
        $routes->add($routeA);
        $matcher = new RouteMatcher($routes, new Configuration());

        $matcher->match(new Request(Request::METHOD_POST, 'path/5'));
    }

    /**
     * @expectedException \Routy\Exceptions\NotFound
     */
    public function testDynamicRouteNotFound()
    {
        $routeA = new Route(
            'GET',
            new RouteData('path/{id}', ['id' => '(\d+)'])
        );

        $routes = new RouteContainer();
        $routes->add($routeA);
        $matcher = new RouteMatcher($routes, new Configuration());

        $matcher->match(new Request(Request::METHOD_GET, 'path/b'));
    }

    public function testSamePathsWithDifferentMethodIsMatched()
    {
        $routeA = new Route('GET',new RouteData('path', []));
        $routeB = new Route('PUT',new RouteData('path', []));

        $routes = new RouteContainer();
        $routes->add($routeA);
        $routes->add($routeB);
        $matcher = new RouteMatcher($routes, new Configuration());

        $match = $matcher->match(new Request(Request::METHOD_GET, 'path'));
        $this->assertSame($routeA, $match->getRoute());

        $match = $matcher->match(new Request(Request::METHOD_PUT, 'path'));
        $this->assertSame($routeB, $match->getRoute());
    }

    public function testSameDynamicPathsWithDifferentMethodIsMatched()
    {
        $routeA = new Route('GET',new RouteData('path/{a}', ['a' => '([^/]+)']));
        $routeB = new Route('PUT',new RouteData('path/{a}', ['a' => '([^/]+)']));

        $routes = new RouteContainer();
        $routes->add($routeA);
        $routes->add($routeB);
        $matcher = new RouteMatcher($routes, new Configuration());

        $match = $matcher->match(new Request(Request::METHOD_GET, 'path/a'));
        $this->assertSame($routeA, $match->getRoute());

        $match = $matcher->match(new Request(Request::METHOD_PUT, 'path/a'));
        $this->assertSame($routeB, $match->getRoute());
    }
}
