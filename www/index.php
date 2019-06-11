<?php

use myMVC\Dispatcher;
use myMVC\LatteEngine;
use myMVC\Router;

require_once "vendor/autoload.php";

$router = new Router($_SERVER["REQUEST_URI"]);
$request = $router->getRequest();

$dispatcher = new Dispatcher($request, new LatteEngine());

$dispatcher->dispatch();

