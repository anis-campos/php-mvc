<?php
require "vendor/autoload.php";

require "Dispatcher.php";
require "Router.php";
require "Request.php";
require "Controller.php";
require "ClassLoader.php";
require "ViewEngine.php";
require "LatteEngine.php";


$register = new ClassLoader();
$register->register();

$router = new Router($_SERVER["REQUEST_URI"]);
$request = $router->getRequest();

$dispatcher = new Dispatcher($request, new LatteEngine());

$dispatcher->dispatch();

