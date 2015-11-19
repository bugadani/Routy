<?php

namespace Routy\Resources;

use Routy\Request;
use Routy\ResourceGenerator;

class PluralResourceGenerator extends ResourceGenerator
{
    /**
     * @var
     */
    private $singularName;

    /**
     * @var
     */
    private $pluralName;

    /**
     * @var
     */
    protected $idPattern;

    public function __construct($owner, $singularName, $pluralName)
    {
        parent::__construct($owner);
        $this->singularName   = $singularName;
        $this->pluralName     = $pluralName;
        $this->controllerName = $pluralName;

        $this->collectionActions = [
            'index'  => Request::METHOD_GET,
            'new'    => Request::METHOD_GET,
            'create' => Request::METHOD_POST
        ];
        $this->memberActions     = [
            'show'    => Request::METHOD_GET,
            'edit'    => Request::METHOD_GET,
            'update'  => Request::METHOD_PUT,
            'destroy' => Request::METHOD_DELETE
        ];
        $this->unnamedActions    = ['index', 'create', 'show', 'update', 'destroy'];
    }

    public function idPattern($idPattern)
    {
        $this->idPattern = $idPattern;

        return $this;
    }

    protected function getPlaceholder($name)
    {
        if ($this->idPattern === null) {
            return "{{$name}}";
        } else {
            return "{{$name}:{$this->idPattern}}";
        }
    }

    /**
     * @param $name
     * @param $method
     * @return $this
     */
    public function member($name, $method)
    {
        if ($this->pluralName !== null) {
            unset($this->unnamedActions[ $name ]);
            $this->memberActions[ $name ] = $method;
        }

        return $this;
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
        return $this->singularName;
    }

    protected function getCollectionNameBase()
    {
        return $this->pluralName;
    }

    protected function getMemberBasePath()
    {
        return $this->pluralName . '/' . $this->getPlaceholder('id');
    }

    protected function getChildBasePath()
    {
        return $this->pluralName . '/' . $this->getPlaceholder($this->singularName . '_id');
    }
}