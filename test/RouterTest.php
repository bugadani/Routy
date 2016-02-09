<?php

namespace Routy\Test;

use Routy\Configuration;
use Routy\Request;
use Routy\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThatAMethodCanOnlyBeAssignedOnce()
    {
        $router = new Router();
        $router->post('path');
        $router->post('path');
    }

    /**
     * This test should throw an exception because, although parameter names are different, their patterns are identical
     *
     * @expectedException \InvalidArgumentException
     */
    public function testThatDynamicPatternsCanOnlyBeAssignedOnce()
    {
        $router = new Router();
        $router->post('path/{id}');
        $router->post('path/{notid}');
    }

    public function testThatMatchIsReturned()
    {
        $router = new Router();
        $router->get('something/{a}/{b}/{c}')->extras(['extra' => 'foo']);

        $match = $router->match(new Request('GET', 'something/a/b/c'));
        $this->assertEquals(
            [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c'
            ],
            $match->getParameters()
        );
        $this->assertEquals(['extra' => 'foo'], $match->getRoute()->getExtras());
    }

    public function testThatRouteCallbackIsCalled()
    {
        $called = false;

        $router = new Router();
        $router->get('something')->onMatch(
            function () use (&$called) {
                $called = true;
            }
        );

        $router->match(new Request('GET', 'something'));

        $this->assertTrue($called);
    }

    public function testThatRouteParametersArePassed()
    {
        $called = false;

        $router = new Router();
        $router->get('something/{a}/{b}/{c}')->onMatch(
            function ($b, $c, $a) use (&$called) {
                $called = true;
                $this->assertEquals('a', $a);
                $this->assertEquals('b', $b);
                $this->assertEquals('c', $c);
            }
        );

        $router->match(new Request('GET', 'something/a/b/c'));

        $this->assertTrue($called);
    }

    public function testThatExtraParametersArePassed()
    {
        $called = false;

        $router = new Router();
        $router->get('something/{a}/{b}/{c}')
               ->extras(['extra' => 'foo'])
               ->onMatch(
                   function ($b, $c, $a, $extra) use (&$called) {
                       $called = true;
                       $this->assertEquals('a', $a);
                       $this->assertEquals('b', $b);
                       $this->assertEquals('c', $c);
                       $this->assertEquals('foo', $extra);
                   }
               );

        $router->match(new Request('GET', 'something/a/b/c'));

        $this->assertTrue($called);
    }

    public function testMultiMethodRoute()
    {
        $called = false;

        $router = new Router();
        $router->add('GET|PUT', 'something/{a}/{b}/{c}')
               ->onMatch(
                   function ($b, $c, $a, $extra) use (&$called) {
                       $called = true;
                       $this->assertEquals('a', $a);
                       $this->assertEquals('b', $b);
                       $this->assertEquals('c', $c);
                       $this->assertEquals('foo', $extra);
                   }
               )->extras(['extra' => 'foo']);

        $router->match(new Request('GET', 'something/a/b/c'));
        $this->assertTrue($called);

        $called = false;

        $router->match(new Request('PUT', 'something/a/b/c'));
        $this->assertTrue($called);
    }

    public function testThatParameterNamesAreHandledCorrectlyRoute()
    {
        $called = false;

        $router = new Router();
        $router->get('something/{a}/{b}/{c}')
               ->onMatch(
                   function ($a, $b, $c, $extra) use (&$called) {
                       $called = true;
                       $this->assertEquals('a', $a);
                       $this->assertEquals('b', $b);
                       $this->assertEquals('c', $c);
                       $this->assertEquals('foo', $extra);
                   }
               )->extras(['extra' => 'foo']);

        $router->put('something/{c}/{b}/{a}')
               ->onMatch(
                   function ($b, $c, $a, $extra) use (&$called) {
                       $called = true;
                       $this->assertEquals('c', $a);
                       $this->assertEquals('b', $b);
                       $this->assertEquals('a', $c);
                       $this->assertEquals('foo', $extra);
                   }
               )->extras(['extra' => 'foo']);

        $router->match(new Request('GET', 'something/a/b/c'));
        $this->assertTrue($called);

        $called = false;

        $router->match(new Request('PUT', 'something/a/b/c'));
        $this->assertTrue($called);
    }

    public function testExtraParametersArePassedToCallback()
    {
        $called = false;

        $router = new Router();
        $router->get('hello')
               ->onMatch(
                   function ($extra = 'default') use (&$called) {
                       $called = true;
                       $this->assertEquals('foobar', $extra);
                   }
               );

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = 'hello?extra=foobar';
        $_GET['extra']             = 'foobar';

        $router->matchCurrentRequest();
        $this->assertTrue($called);
    }

    public function testBasePath()
    {
        $called = false;

        $config           = new Configuration();
        $config->basePath = 'base/';

        $router = new Router($config);
        $router->get('hello')
               ->name('hello')
               ->onMatch(
                   function () use (&$called) {
                       $called = true;
                   }
               );

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = 'base/hello';

        $router->matchCurrentRequest();
        $this->assertTrue($called);

        $this->assertEquals('base/hello', $router->to('hello'));
    }
}
