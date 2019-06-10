<?php

require_once "vendor/autoload.php";
require_once "Dispatcher.php";
require_once "Router.php";
require_once "Request.php";
require_once "Controller.php";
require_once "ModelLoader.php";
require_once "ViewEngine.php";
require_once "LatteEngine.php";


$register = new ModelLoader();
$register->register();

$router = new Router($_SERVER["REQUEST_URI"]);
$request = $router->getRequest();

$dispatcher = new Dispatcher($request, new LatteEngine());

$dispatcher->dispatch();

