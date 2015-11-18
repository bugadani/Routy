<?php

namespace Routy\Test;

use Routy\Configuration;
use Routy\Exceptions\MethodNotAllowed;
use Routy\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testShortGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/some/query?other=parameter';
        $_GET                      = [
            'other' => 'parameter'
        ];

        $request = Request::fromCurrentRequest();

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/some/query', $request->getPath());
    }

    public function testNotShortGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET = [
            'q'     => '/some/query',
            'other' => 'parameter'
        ];

        $config               = new Configuration();
        $config->useShortUrls = false;
        $config->pathKey      = 'q';

        $request = Request::fromCurrentRequest($config);

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/some/query', $request->getPath());
    }

    public function testPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/some/query';

        $request = Request::fromCurrentRequest();

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/some/query', $request->getPath());
    }

    public function testEmulatedPutRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/some/query';
        $_POST['_method']          = 'PUT';

        $request = Request::fromCurrentRequest();

        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/some/query', $request->getPath());
    }

    /**
     * @expectedException \Routy\Exceptions\MethodNotAllowed
     */
    public function testEmulatedRequestException()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/some/query';
        $_POST['_method']          = 'PUT';

        $config = new Configuration();
        $config->emulatedMethods = ['DELETE'];
        $request = Request::fromCurrentRequest($config);

        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/some/query', $request->getPath());
    }
}
