<?php

namespace Routy\Test;

use Routy\Configuration;
use Routy\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function requestProvider()
    {
        return [
            'get with extra parameters' => [
                'request'     => [
                    'SERVER' => [
                        'REQUEST_METHOD' => 'GET',
                        'REQUEST_URI'    => '/some/query?other=parameter'
                    ],
                    'GET'    => ['other' => 'parameter']
                ],
                'config'      => [],
                'expectation' => [
                    'method' => 'GET',
                    'path'   => '/some/query',
                    'extras' => [
                        'other' => 'parameter'
                    ]
                ]
            ],
            'simple post'               => [
                'request'     => [
                    'SERVER' => [
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI'    => '/some/query'
                    ]
                ],
                'config'      => [],
                'expectation' => [
                    'method' => 'POST',
                    'path'   => '/some/query',
                    'extras' => []
                ]
            ],
            'emulated put'              => [
                'request'     => [
                    'SERVER' => [
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI'    => '/some/query'
                    ],
                    'POST'   => [
                        '_method' => 'PUT'
                    ]
                ],
                'config'      => [],
                'expectation' => [
                    'method' => 'PUT',
                    'path'   => '/some/query',
                    'extras' => []
                ]
            ],
            'get when no short urls'    => [
                'request'     => [
                    'SERVER' => [
                        'REQUEST_METHOD' => 'GET',
                        'REQUEST_URI'    => 'index.php?q=/some/query&other=parameter'
                    ],
                    'GET'    => [
                        'q'     => '/some/query',
                        'other' => 'parameter'
                    ]
                ],
                'config'      => [
                    'useShortUrls' => false,
                    'pathKey'      => 'q'
                ],
                'expectation' => [
                    'method' => 'GET',
                    'path'   => '/some/query',
                    'extras' => [
                        'other' => 'parameter'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $config
     * @return null|Configuration
     */
    private function createConfiguration(array $config)
    {
        if (!empty($config)) {
            $configuration = new Configuration();
            foreach ($config as $k => $v) {
                $configuration->$k = $v;
            }
        } else {
            $configuration = null;
        }

        return $configuration;
    }

    /**
     * @dataProvider requestProvider
     */
    public function testFromCurrentRequest($request, $config, $expectation)
    {
        $_SERVER = isset($request['SERVER']) ? $request['SERVER'] : [];
        $_GET    = isset($request['GET']) ? $request['GET'] : [];
        $_POST   = isset($request['POST']) ? $request['POST'] : [];

        $configuration = $this->createConfiguration($config);

        $req = Request::fromCurrentRequest($configuration);

        $this->assertEquals($expectation['method'], $req->getMethod());
        $this->assertEquals($expectation['path'], $req->getPath());
        $this->assertEquals($expectation['extras'], $req->getExtras());
    }

    /**
     * @expectedException \Routy\Exceptions\MethodNotAllowed
     */
    public function testEmulateDisallowedRequestThrowsException()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/some/query';
        $_POST['_method']          = 'PUT';

        $config                  = new Configuration();
        $config->emulatedMethods = ['DELETE', 'GET'];
        Request::fromCurrentRequest($config);
    }

    /**
     * @expectedException \Routy\Exceptions\MethodNotAllowed
     */
    public function testEmulateDisallowedRequestWhenTheMethodMatchesThrowsException()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/some/query';
        $_POST['_method']          = 'PUT';

        $config                  = new Configuration();
        $config->emulatedMethods = ['POST'];
        Request::fromCurrentRequest($config);
    }
}
