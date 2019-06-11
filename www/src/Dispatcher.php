<?php

namespace myMVC;

class Dispatcher
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Controller
     */
    private $controller;
    /**
     * @var string
     */
    private $controllerName;
    /**
     * @var ViewEngine
     */
    private $viewEngine;

    public function __construct($request, $viewEngine)
    {
        $this->request = $request;
        $this->loadController($this->request->getController());
        $this->viewEngine = $viewEngine;
    }

    private function loadController($controller)
    {
        $name = __NAMESPACE__ . '\\' . $controller . "Controller";
        $this->controllerName = $name;
    }

    public function dispatch()
    {

        if (!class_exists($this->controllerName)) {
            echo "The controller '{$this->controllerName}' is missing'";
            return;
        }

        $this->controller = new $this->controllerName();

        $action = $this->request->getAction();
        if (!method_exists($this->controller, $action)) {
            echo "The controller '{$this->controllerName}' is has no action '{$action}'";
            return;
        }

        $this->controller->invoke($this->request, $this->viewEngine);
    }
}

