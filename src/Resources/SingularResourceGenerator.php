<?php

namespace Routy\Resources;

use Routy\Request;
use Routy\ResourceGenerator;
use Routy\Router;

class SingularResourceGenerator extends ResourceGenerator
{
    /**
     * @var
     */
    private $name;

    public function __construct(Router $owner, $name)
    {
        parent::__construct($owner);
        $this->name           = $name;
        $this->controllerName = $name;

        $this->collectionActions = [
            'new'     => Request::METHOD_GET,
            'create'  => Request::METHOD_POST,
            'show'    => Request::METHOD_GET,
            'edit'    => Request::METHOD_GET,
            'update'  => Request::METHOD_PUT,
            'destroy' => Request::METHOD_DELETE
        ];
        $this->memberActions     = [];
        $this->unnamedActions    = ['create', 'show', 'update', 'destroy'];
    }

    /**
     * @param $name
     * @param $method
     * @return $this
     */
    public function collection($name, $method)
    {
        unset($this->unnamedActions[ $name ]);
        $this->collectionActions[ $name ] = $method;

        return $this;
    }

    protected function getMemberNameBase()
    {
        return $this->name;
    }

    protected function getCollectionNameBase()
    {
        return $this->name;
    }

    protected function getMemberBasePath()
    {
        return $this->name;
    }

    protected function getChildBasePath()
    {
        return $this->name;
    }
}