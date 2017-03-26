<?php

namespace Typedoc;

use Typedoc\Data\Api;
use Typedoc\Data\Test;
use Typedoc\Data\Model;
use Typedoc\Data\Route;

class Data
{
    private $controllers;
    private $classes;
    private $dir;

    public function __construct(array $controllers, array $classes, $dir)
    {
        $this->classes = $classes;
        $this->controllers = $controllers;
        $this->dir = $dir;
    }

    public function getRoute()
    {
        $controllers = $this->wrapFilePath($this->controllers);
        $res = new Route();
        $res = $res->handle($controllers, $this->classes);
        return $res;
    }

    public function getModel()
    {
        $controllers = $this->wrapFilePath($this->controllers);
        $res = new Model();
        $res = $res->handle($controllers, $this->classes);
        return $res;
    }

    public function getTest()
    {
        $controllers = $this->wrapFilePath($this->controllers);
        $res = new Test();
        $res = $res->handle($controllers, $this->classes);
        return $res;
    }

    public function getApi()
    {
        $controllers = $this->wrapFilePath($this->controllers);
        $res = new Api();
        $res = $res->handle($controllers, $this->classes);
        return $res;
    }

    /**
     * @param array $controllers
     * @return array
     */
    private function wrapFilePath(array $controllers)
    {
        foreach ($controllers as $k => $controller) {
            $controllers[$k] = ($this->dir . $controller . '.ts');
        }

        return $controllers;
    }
}