<?php

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

    public function __construct($request)
    {
        $this->request = $request;
        $this->loadController($this->request->getController());
    }

    private function loadController($controller)
    {
        $name = $controller . "Controller";
        $file = './Controllers/' . $name . '.php';
        if (file_exists($file))
            require($file);
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

        $this->controller->invoke($this->request);
    }
}

?>