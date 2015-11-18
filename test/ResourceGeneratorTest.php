<?php

namespace Routy\Test;

use Routy\Configuration;
use Routy\Request;
use Routy\Route;
use Routy\RouteContainer;
use Routy\Router;

class ResourceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouteContainer
     */
    private $container;

    public function setUp()
    {
        $this->container        = new RouteContainer();
        $config                 = new Configuration();
        $config->routeContainer = $this->container;
        $this->router           = new Router($config);
    }

    public function testSingularResourceGeneration()
    {
        $this->router->resource('singular');

        $this->checkGeneratedResources(
            [
                'singular'      => ['GET', 'POST', 'DELETE', 'PUT'],
                'singular/new'  => ['GET'],
                'singular/edit' => ['GET'],
            ],
            [
                'singular_url'      => 'singular',
                'new_singular_url'  => 'singular/new',
                'edit_singular_url' => 'singular/edit'
            ]
        );
    }

    public function testPluralResourceGeneration()
    {
        $this->router->resources('singular', 'plural');

        $this->checkGeneratedResources(
            [
                'plural'           => ['GET', 'POST'],
                'plural/new'       => ['GET'],
                'plural/{id}'      => ['GET', 'DELETE', 'PUT'],
                'plural/{id}/edit' => ['GET'],
            ],
            [
                'plural_url'        => 'plural',
                'singular_url'      => 'plural/{id}',
                'new_singular_url'  => 'plural/new',
                'edit_singular_url' => 'plural/{id}/edit'
            ]
        );
    }

    public function testPluralResourceWithChildrenGeneration()
    {
        $otherChild = $this->router->resources('other_child', 'other_children');
        $this->router->resources('singular', 'plural')
                     ->children(
                         $this->router->resources('child', 'children')
                                      ->child($this->router->resources('grandchild', 'grandchildren')),
                         $otherChild
                     );

        $this->checkGeneratedResources(
            [
                'plural'           => ['GET', 'POST'],
                'plural/new'       => ['GET'],
                'plural/{id}'      => ['GET', 'DELETE', 'PUT'],
                'plural/{id}/edit' => ['GET'],

                'plural/{singular_id}/children'           => ['GET', 'POST'],
                'plural/{singular_id}/children/new'       => ['GET'],
                'plural/{singular_id}/children/{id}'      => ['GET', 'DELETE', 'PUT'],
                'plural/{singular_id}/children/{id}/edit' => ['GET'],

                'plural/{singular_id}/other_children'           => ['GET', 'POST'],
                'plural/{singular_id}/other_children/new'       => ['GET'],
                'plural/{singular_id}/other_children/{id}'      => ['GET', 'DELETE', 'PUT'],
                'plural/{singular_id}/other_children/{id}/edit' => ['GET'],

                'plural/{singular_id}/children/{child_id}/grandchildren'           => ['GET', 'POST'],
                'plural/{singular_id}/children/{child_id}/grandchildren/new'       => ['GET'],
                'plural/{singular_id}/children/{child_id}/grandchildren/{id}'      => ['GET', 'DELETE', 'PUT'],
                'plural/{singular_id}/children/{child_id}/grandchildren/{id}/edit' => ['GET'],
            ],
            [
                'plural_url'        => 'plural',
                'singular_url'      => 'plural/{id}',
                'new_singular_url'  => 'plural/new',
                'edit_singular_url' => 'plural/{id}/edit',

                'singular_children_url'   => 'plural/{singular_id}/children',
                'singular_child_url'      => 'plural/{singular_id}/children/{id}',
                'new_singular_child_url'  => 'plural/{singular_id}/children/new',
                'edit_singular_child_url' => 'plural/{singular_id}/children/{id}/edit',

                'singular_child_grandchildren_url'   => 'plural/{singular_id}/children/{child_id}/grandchildren',
                'singular_child_grandchild_url'      => 'plural/{singular_id}/children/{child_id}/grandchildren/{id}',
                'new_singular_child_grandchild_url'  => 'plural/{singular_id}/children/{child_id}/grandchildren/new',
                'edit_singular_child_grandchild_url' => 'plural/{singular_id}/children/{child_id}/grandchildren/{id}/edit',

                'singular_other_children_url'   => 'plural/{singular_id}/other_children',
                'singular_other_child_url'      => 'plural/{singular_id}/other_children/{id}',
                'new_singular_other_child_url'  => 'plural/{singular_id}/other_children/new',
                'edit_singular_other_child_url' => 'plural/{singular_id}/other_children/{id}/edit'
            ]
        );
    }

    public function testShallowResourceGeneration()
    {
        $this->router->resources('parent', 'parents')
                     ->child(
                         $this->router->resources('child', 'children')
                                      ->shallow()
                     );

        $this->checkGeneratedResources(
            [
                'parents'                          => ['GET', 'POST'],
                'parents/new'                      => ['GET'],
                'parents/{id}'                     => ['GET', 'DELETE', 'PUT'],
                'parents/{id}/edit'                => ['GET'],
                'parents/{parent_id}/children'     => ['GET', 'POST'],
                'parents/{parent_id}/children/new' => ['GET'],
                'children/{id}'                    => ['GET', 'DELETE', 'PUT'],
                'children/{id}/edit'               => ['GET'],
            ],
            [
                'parents_url'          => 'parents',
                'parent_url'           => 'parents/{id}',
                'new_parent_url'       => 'parents/new',
                'edit_parent_url'      => 'parents/{id}/edit',
                'parent_children_url'  => 'parents/{parent_id}/children',
                'new_parent_child_url' => 'parents/{parent_id}/children/new',
                'child_url'            => 'children/{id}',
                'edit_child_url'       => 'children/{id}/edit'
            ]
        );
    }

    public function testPrefixedShallowResourceGeneration()
    {
        $this->router->resources('parent', 'parents')
                     ->child(
                         $this->router->resources('child', 'children')
                                      ->shallow(true)
                                      ->shallowPath('path')
                                      ->shallowPrefix('prefix')
                     )
                     ->child(
                         $this->router->resources('unprefixed_child', 'unprefixed_children')
                                      ->shallow(true)
                     );

        $this->checkGeneratedResources(
            [
                'parents'           => ['GET', 'POST'],
                'parents/new'       => ['GET'],
                'parents/{id}'      => ['GET', 'DELETE', 'PUT'],
                'parents/{id}/edit' => ['GET'],

                'parents/{parent_id}/children'     => ['GET', 'POST'],
                'parents/{parent_id}/children/new' => ['GET'],
                'path/children/{id}'               => ['GET', 'DELETE', 'PUT'],
                'path/children/{id}/edit'          => ['GET'],

                'parents/{parent_id}/unprefixed_children'     => ['GET', 'POST'],
                'parents/{parent_id}/unprefixed_children/new' => ['GET'],
                'unprefixed_children/{id}'                    => ['GET', 'DELETE', 'PUT'],
                'unprefixed_children/{id}/edit'               => ['GET'],
            ],
            [
                'parents_url'     => 'parents',
                'parent_url'      => 'parents/{id}',
                'new_parent_url'  => 'parents/new',
                'edit_parent_url' => 'parents/{id}/edit',

                'parent_children_url'   => 'parents/{parent_id}/children',
                'new_parent_child_url'  => 'parents/{parent_id}/children/new',
                'prefix_child_url'      => 'path/children/{id}',
                'edit_prefix_child_url' => 'path/children/{id}/edit',

                'parent_unprefixed_children_url'  => 'parents/{parent_id}/unprefixed_children',
                'new_parent_unprefixed_child_url' => 'parents/{parent_id}/unprefixed_children/new',
                'unprefixed_child_url'            => 'unprefixed_children/{id}',
                'edit_unprefixed_child_url'       => 'unprefixed_children/{id}/edit'
            ]
        );
    }

    public function testExceptOnly()
    {
        $this->router->resources('parent', 'parents')
                     ->only('index', 'update')
                     ->except('update');

        $this->checkGeneratedResources(
            ['parents' => ['GET']],
            ['parents_url' => 'parents']
        );
    }

    public function testMemberAndCollection()
    {
        $this->router->resources('parent', 'parents')
                     ->only('index')
                     ->member('foo', 'GET')
                     ->collection('cfoo', 'PUT');

        $this->router->resource('singular')
                     ->only('show')
                     ->collection('sing_foo', 'DELETE');

        $this->checkGeneratedResources(
            [
                'parents'           => ['GET'],
                'parents/{id}/foo'  => ['GET'],
                'parents/cfoo'      => ['PUT'],
                'singular'          => ['GET'],
                'singular/sing_foo' => ['DELETE']
            ],
            [
                'parents_url'           => 'parents',
                'cfoo_parent_url'       => 'parents/cfoo',
                'foo_parent_url'        => 'parents/{id}/foo',
                'singular_url'          => 'singular',
                'sing_foo_singular_url' => 'singular/sing_foo'
            ]
        );
    }

    private function checkGeneratedResources($methodArray, $namedArray)
    {
        /** @var Route $route */
        foreach ($this->container as $route) {
            $path      = $route->getParsed()->getPath();
            $methods   = $methodArray[ $path ];
            $methodKey = array_search($route->getMethod(), $methods, true);
            unset($methodArray[ $path ][ $methodKey ]);

            if (empty($methodArray[ $path ])) {
                unset($methodArray[ $path ]);
            }
        }

        if (!empty($methodArray)) {
            $this->fail("Some routes have not been generated");
        }

        foreach ($namedArray as $name => $path) {
            $route = $this->container->get($name);

            $this->assertEquals($path, $route->getParsed()->getPath());
        }
    }

    public function testCallbacksAreCalled()
    {
        $called = false;
        $this->router->resource('singular')
                     ->onMatch(
                         function ($action) use (&$called) {
                             $this->assertEquals('edit', $action);
                             $called = true;
                         }
                     );

        $this->router->match(new Request('GET', 'singular/edit'));
        $this->assertTrue($called);
    }
}
