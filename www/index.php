<?php
require __DIR__ . '/vendor/autoload.php';

require("Dispatcher.php");
require("Router.php");
require("Request.php");
require("Controller.php");


$request = Router::parse($_SERVER["REQUEST_URI"]);

$dispatcher = new Dispatcher($request);

$dispatcher->dispatch();

