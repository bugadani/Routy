<?php

namespace Routy;

use Routy\Invokers\DelegateInterface;
use Routy\Invokers\ResourceDelegate;

abstract class ResourceGenerator
{
    /**
     * @var Router
     */
    protected $owner;

    //Action properties
    protected $collectionActions;
    protected $memberActions;
    protected $unnamedActions;
    protected $controllerName;

    private $shallow;
    private $shallowPrefix = '';
    private $shallowPath   = '';
    private $isChild       = false;

    /**
     * @var ResourceGenerator[]
     */
    protected $children = [];
    private   $onMatch;

    public function __construct(Router $owner)
    {
        $this->owner = $owner;
    }

    public function __destruct()
    {
        if (!$this->isChild) {
            $this->generateRoutes('', '');
        }
    }

    /**
     * @param $except
     *
     * @return $this
     */
    public function except($except)
    {
        $except                  = array_flip(func_get_args());
        $this->collectionActions = array_diff_key($this->collectionActions, $except);
        $this->memberActions     = array_diff_key($this->memberActions, $except);

        return $this;
    }

    /**
     * @param $only
     *
     * @return $this
     */
    public function only($only)
    {
        $only                    = array_flip(func_get_args());
        $this->collectionActions = array_intersect_key($this->collectionActions, $only);
        $this->memberActions     = array_intersect_key($this->memberActions, $only);

        return $this;
    }

    public function child(ResourceGenerator $child)
    {
        $child->isChild   = true;
        $this->children[] = $child;

        return $this;
    }

    public function children(ResourceGenerator $child)
    {
        array_map([$this, 'child'], func_get_args());

        return $this;
    }

    public function shallow($shallow = true)
    {
        $this->shallow = $shallow;

        return $this;
    }

    public function shallowPrefix($prefix)
    {
        $this->shallowPrefix = $prefix;
        if (strlen($prefix) > 0) {
            $this->shallowPrefix .= '_';
        }

        return $this;
    }

    public function shallowPath($path)
    {
        $this->shallowPath = $path;
        if (strlen($path) > 0) {
            $this->shallowPath .= '/';
        }

        return $this;
    }

    public function onMatch($onMatch)
    {
        if (!$onMatch instanceof DelegateInterface) {
            $onMatch = new ResourceDelegate($onMatch);
        }
        $this->onMatch = $onMatch;
    }

    protected abstract function getMemberNameBase();

    protected abstract function getCollectionNameBase();

    protected abstract function getMemberBasePath();

    protected abstract function getChildBasePath();

    private function generateRoutes($nameBase, $pathBase)
    {
        if ($this->shallow) {
            $memberPathBase        = $this->shallowPath . $this->getMemberBasePath();
            $memberNameBase        = $this->shallowPrefix . $this->getMemberNameBase();
            $unnamedMemberNameBase = $this->shallowPrefix . $this->getMemberNameBase();
        } else {
            $memberPathBase        = $pathBase . $this->getMemberBasePath();
            $memberNameBase        = $nameBase . $this->getMemberNameBase();
            $unnamedMemberNameBase = $nameBase . $this->getMemberNameBase();
        }
        $collectionPathBase        = $pathBase . $this->getCollectionNameBase();
        $collectionNameBase        = $nameBase . $this->getMemberNameBase();
        $unnamedCollectionNameBase = $nameBase . $this->getCollectionNameBase();

        $this->addRoutes(
            $this->collectionActions,
            $collectionNameBase . '_url',
            $unnamedCollectionNameBase . '_url',
            $collectionPathBase
        );
        $this->addRoutes(
            $this->memberActions,
            $memberNameBase . '_url',
            $unnamedMemberNameBase . '_url',
            $memberPathBase
        );

        if (!empty($this->children)) {
            $childPathBase = $pathBase . $this->getChildBasePath() . '/';
            $childNameBase = $memberNameBase . '_';
            foreach ($this->children as $child) {
                $child->generateRoutes($childNameBase, $childPathBase);
            }
        }
    }

    private function addRoutes($actions, $namedActionName, $unnamedActionName, $actionPathBase)
    {
        $unnamedAdded = false;
        foreach ($actions as $action => $method) {
            if (in_array($action, $this->unnamedActions)) {
                $initializer = $this->owner->add($method, $actionPathBase);
                if (!$unnamedAdded) {
                    $unnamedAdded = true;

                    $initializer->name($unnamedActionName);
                }
            } else {
                $initializer = $this->owner
                    ->add($method, $actionPathBase . '/' . $action)
                    ->name($action . '_' . $namedActionName);
            }
            $initializer->extras(
                [
                    'controller' => $this->controllerName,
                    'action'     => $action
                ]
            );
            if ($this->onMatch !== null) {
                $initializer->onMatch($this->onMatch);
            }
        }
    }
}