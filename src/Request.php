<?php

namespace Routy;

use Routy\Exceptions\MethodNotAllowed;

/**
 * A simple request class that processes the current HTTP request for RouteMatcher
 */
class Request
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';

    public static function fromCurrentRequest(Configuration $configuration = null)
    {
        if ($configuration === null) {
            $configuration = new Configuration();
        }

        $method = $_SERVER['REQUEST_METHOD'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $configuration->emulateMethodsWhenPost) {
            $emulateMethodKey = $configuration->emulateMethodsKey;
            if (isset($_POST[ $emulateMethodKey ])) {
                self::guardEmulatedMethod($configuration, $emulateMethodKey);
                $method = $_POST[ $emulateMethodKey ];
                unset($_POST[ $emulateMethodKey ]);
            }
        }

        if ($configuration->useShortUrls) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        } else {
            $pathKey = $configuration->pathKey;
            $path    = isset($_GET[ $pathKey ]) ? $_GET[ $pathKey ] : '';
            unset($_GET[ $pathKey ]);
        }

        return new Request($method, $path, $_GET);
    }

    /**
     * @param Configuration $configuration
     * @param $emulateMethodKey
     */
    private static function guardEmulatedMethod(Configuration $configuration, $emulateMethodKey)
    {
        if (!in_array($_POST[ $emulateMethodKey ], $configuration->emulatedMethods)) {
            $allowed = $configuration->emulatedMethods;
            if (count($allowed) > 1) {
                $last    = array_pop($allowed);
                $methods = join(', ', $allowed) . ' and ' . $last;
                throw new MethodNotAllowed("Only {$methods} are allowed as emulated methods");
            } else {
                $method = reset($allowed);
                throw new MethodNotAllowed("Only {$method} is allowed as emulated method");
            }
        }
    }

    private $method;
    private $path;
    /**
     * @var array
     */
    private $extras;

    public function __construct($method, $path, array $extras = [])
    {
        $this->method = $method;
        $this->path   = $path;
        $this->extras = $extras;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }
}