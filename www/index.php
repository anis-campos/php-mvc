<?php
require "vendor/autoload.php";

require "Dispatcher.php";
require "Router.php";
require "Request.php";
require "Controller.php";
require "Controller.php";
require "ClassLoader.php";


$register = new ClassLoader();
$register->register();

$request = Router::parse($_SERVER["REQUEST_URI"]);

$dispatcher = new Dispatcher($request);

$dispatcher->dispatch();

