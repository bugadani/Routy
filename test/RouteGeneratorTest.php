<?php

namespace Routy\Test;

use Routy\Configuration;
use Routy\Router;

class RouteGeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMissingParametersException()
    {
        $router = new Router();
        $router->get('something/{a}/{b}/{c}')->name('namedRoute');

        $router->to(
            'namedRoute',
            [
                'a' => 'paramA'
            ]
        );
    }

    public function testGenerateRoute()
    {
        $config               = new Configuration();
        $config->useShortUrls = false;
        $config->pathKey      = 'q';

        $router = new Router($config);
        $router->get('something/{a}/{b}/{c}')->name('namedRoute');

        $generated = $router->to(
            'namedRoute',
            [
                'a'     => 'paramA',
                'c'     => 'paramC',
                'b'     => 'paramB',
                'extra' => 'foobar',
                'other' => 'baz'
            ]
        );

        $this->assertEquals('?q=something/paramA/paramB/paramC&extra=foobar&other=baz', $generated);
    }

    public function testGenerateShortRoute()
    {
        $router = new Router();
        $router->get('something/{a}/{b}/{c}')->name('namedRoute');

        $generated = $router->to(
            'namedRoute',
            [
                'a'     => 'paramA',
                'c'     => 'paramC',
                'b'     => 'paramB',
                'extra' => 'foobar',
                'other' => 'baz'
            ]
        );

        $this->assertEquals('something/paramA/paramB/paramC?extra=foobar&other=baz', $generated);
    }
}
