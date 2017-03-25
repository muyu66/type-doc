<?php

namespace Typedoc;

use Typedoc\Data\Api;
use Typedoc\Data\Test;

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

    public function get()
    {
        $controllers = $this->wrapFilePath($this->controllers);

        $api = new Api();
        $api = $api->handle($controllers, $this->classes);

        $test = new Test();
        $test = $test->handle($controllers, $this->classes);

        return $test;
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